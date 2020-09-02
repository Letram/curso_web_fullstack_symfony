<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserRegister;
use App\Service\JwtAuth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
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
     */
    public function register(Request $request, ValidatorInterface $validator)
    {
        $requestObj = $request->request;

        $new_user = $this->getUserFromRequest($requestObj, true);

        $errors = $validator->validate($new_user);
        if ($errors->count() > 0) {
            return $this->json([
                "code" => 400,
                "errors" => $errors,
                "status" => -1,
            ], 400);
        }

        //Ya con los datos validados, podemos cifrar la contraseña para pasarla luego a la BD
        $new_user->setPassword(hash('sha256', $new_user->getPassword()));

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
                "code" => "200",
                "status" => 1,
                "user" => $new_user,
            ], 200);
        }
    }

    /**
     * @Route("/auth/login", methods={"POST"}, name="login")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \App\Service\JwtAuth $auth
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function login(Request $request, JwtAuth $auth)
    {

        $requestObj = $request->request;
        $user_to_login = $this->getUserFromRequest($requestObj, false);
        $retrieve_token = $requestObj->get("retrieve_token", "");
        if (! isset($user_to_login)) {
            return $this->json([
                "code" => 400,
                "errors" => "Error logueando un usuario",
                "status" => -1,
            ], 400);
        }

        $projectRoot = $this->getParameter('kernel.project_dir');
        $validator = Validation::createValidatorBuilder()->addYamlMapping($projectRoot."/config/validation/login_validation.yaml")->getValidator();
        $errors = $validator->validate($user_to_login);
        if ($errors->count() > 0) {
            return $this->json([
                "code" => 400,
                "errors" => $errors,
                "status" => -1,
            ], 400);
        }

        //Si se ha validado, ciframos la contraseña para poder buscar luego la tupla (email,password) en la BD
        $user_to_login->setPassword(hash('sha256', $user_to_login->getPassword()));

        $login_attempt = $auth->signup($user_to_login, $retrieve_token);
        if (count($login_attempt) <= 0) {
            return $this->generateJsonErrorResponse(400, ["Username or password incorrect."]);
        }

        return $this->json([
            "method" => "login",
            "message" => $login_attempt,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\InputBag $requestObj
     * @param bool $is_register
     * @return null|User
     */
    private function getUserFromRequest(InputBag $requestObj, bool $is_register)
    {
        if (count($requestObj) <= 0) {
            return null;
        }
        $user_in_request = new User();
        $user_in_request->setName($requestObj->get('name', ""));
        $user_in_request->setSurname($requestObj->get('surname', ""));
        $user_in_request->setPassword($requestObj->get('password', ""));
        $user_in_request->setEmail($requestObj->get('email', ""));

        return $user_in_request;
    }

    /**
     * @param int $error_code
     * @param array $errors
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    private function generateJsonErrorResponse(int $error_code, array $errors)
    {
        return $this->json([
            "code" => $error_code,
            "errors" => $errors,
            "status" => -1,
        ], $error_code);
    }
}
