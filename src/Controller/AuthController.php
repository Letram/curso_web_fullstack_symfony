<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserRegister;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\HttpClient\Exception;

class AuthController extends AbstractController
{
    /**
     * @Route("/auth", name="auth")
     */
    public function index()
    {
        return $this->render('auth/index.html.twig', [
            'controller_name' => 'AuthController',
        ]);
    }

    /**
     * @Route("/auth/register", methods={"POST"}, name="register")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Symfony\Component\HttpClient\Exception\JsonException
     */
    public function register(Request $request, ValidatorInterface $validator)
    {
        $requestObj = $request->request;

        $new_user = new User();
        $new_user->setName($requestObj->get('name', ""));
        $new_user->setSurname($requestObj->get('surname', ""));
        $new_user->setPassword(hash('sha256', $requestObj->get('password', "")));
        $new_user->setEmail($requestObj->get('email', ""));

        $errors = $validator->validate($new_user);
        if ($errors->count() > 0) {
            return $this->json([
                "code" => 400,
                "errors" => $errors,
                "status" => -1,
            ], 400);
        }

        //Antes de guardar el usuario, tenemos que comprobar que no se está realizando una creación con un correo o
        // datos que ya existen en la BD. Para ello tenemos que acceder al manager de Doctrine, el encargado de dejarnos
        // realizar operaciones con la BD de inserción y actualización. Es como el Eloquent de Laravel pero peor.
        // También usamos el repositorio de usuarios para poder comprobar la existencia de usuarios repetidos.
        // (con el mismo email)

        //El manager se encarga de operaciones que requieran persistencia y el repositorio para consultas de lectura.
        $entity_manager = $this->getDoctrine()->getManager();
        $user_repo = $this->getDoctrine()->getRepository(User::class);

        if ($user_repo->findOneBy([
            "email" => $new_user->getEmail(),
        ])) {
            //usuario que ya existe
            return $this->json([
                "code" => 400,
                "errors" => [
                    "violations" => [
                        [
                            "propertyPath" => "email",
                            "title" => "Email already in use.",
                        ],
                    ],
                ],
                "status" => -1,
            ], 400);
        } else {
            //guardamos el usuario

            //Le decimos a Doctrine que queremos ejecutar una operación de inserción (todavía no se ha hecho nada, es como un almacenamiento en cola.)
            $entity_manager->persist($new_user);

            //Ejecutamos todas las operaciones que hemos encolado.
            $entity_manager->flush();

            return $this->json([
                "method" => "register",
                "message" => ["successful"],
                "user" => $new_user,
            ], 200);
        }
    }
}
