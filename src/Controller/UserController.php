<?php

namespace App\Controller;

use App\Entity\User;
use App\Kernel;
use App\Service\JwtAuth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index()
    {
        //esto sería como el Eloquent de Laravel pero para Symfony. Usa Doctrine como equivaliente. Tenemos que instanciarlo y obtener el repositorio (como la tabla) para poder usar los métodos.
        $user_repo = $this->getDoctrine()->getRepository(User::class);

        //para evitar el problema de la navegación circular, ver la clase de User y la serialización de JSON
        return $this->json([
            "method" => "index",
            "users" => $user_repo->findAll(),
        ]);
    }

    /**
     * @Route("user/edit", methods={"PUT"}, name="user.edit")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \App\Service\JwtAuth $auth
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * Para poder editar los datos de un usuario, primero tenemos que verificar que viaja un token válido en la cabecera Authorization de la request
     */
    public function edit(Request $request, JwtAuth $auth)
    {
        $auth_token = $request->headers->get("Authorization");

        if (! isset($auth_token)) {
            return $this->generateJsonErrorResponse(403, ["Token not valid"]);
        }

        $params = $request->request->all();
        unset($params["retrieve_token"]);

        $decoded_token = $auth->isTokenValid($auth_token);
        if (! isset($decoded_token)) {
            return $this->generateJsonErrorResponse(403, ["Token not valid"]);
        }

        //Como queremos hacer una actualización de datos, necesitamos el Entity Manager ya que vamos a realizar operaciones de escritura.
        //Por otra parte, para poder conseguir todos los datos del usuario de la base de datos, necesitamos el repositorio de usuarios también (operación de lectura).
        $entity_manager = $this->getDoctrine()->getManager();
        $user_repository = $this->getDoctrine()->getRepository(User::class);

        $current_user = $user_repository->findOneBy(["id" => $decoded_token->user->id]);

        $request_user_data = $this->getUserFromRequest($request->request, false);

        //No sé por qué pero no carga la información bien de los directorios, así que lo tengo que cargar a mano.
        $projectRoot = $this->getParameter('kernel.project_dir');
        $validator_builder = Validation::createValidatorBuilder()->addYamlMapping($projectRoot."/config/validation/edit_user_validation.yaml");

        $errors = $validator_builder->getValidator()->validate($request_user_data);

        if (count($errors) > 0) {
            return $this->generateJsonErrorResponse(400, (array) $errors);
        }

        //En el caso de que todos los datos se hayan validado correctamente, podemos pasasr a la parte del guardado de datos.
        //primero tenemos que comprobar que el correo no exista ya en la BD o que el correo que nos ha llegado sea igual al que hemos recuperado
        if (count($user_repository->findBy(["email" => $request_user_data->getEmail()])) == 0 || $current_user->getEmail() == $request_user_data->getEmail()) {

            //Recorremos todos los parámetros que queremos cambiar que están en la request y llamamos al setter de cada uno
            foreach ($params as $key => $value) {
                call_user_func_array([$current_user, 'set'.ucfirst($key)], [$value]);
            }
            $entity_manager->flush();
        }

        return $this->json([
            "code" => 200,
            "status" => 1,
            "user" => $current_user,
        ], 200);
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
