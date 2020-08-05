<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

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
            "users" => $user_repo->findAll()
        ]);
    }
}
