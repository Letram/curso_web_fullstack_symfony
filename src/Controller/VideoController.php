<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Video;
use App\Service\JwtAuth;
use Cassandra\Exception\WriteTimeoutException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class VideoController extends AbstractController
{
    /**
     * @Route("/video", name="video")
     */
    public function index()
    {

        $video_repo = $this->getDoctrine()->getRepository(Video::class);

        return $this->json([
            "method" => "index",
            "videos" => $video_repo->findAll(),
        ]);
    }

    /**
     * @Route("/video/create", methods={"POST"}, name="video.create")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Service\JwtAuth $auth
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function create(Request $request, ValidatorInterface $validator, JwtAuth $auth)
    {
        $auth_token = $request->headers->get("Authorization");

        if (! isset($auth_token)) {
            return $this->generateJsonErrorResponse(400, ["Auth token missing"]);
        }

        $decoded_token = $auth->isTokenValid($auth_token);

        if (! isset($decoded_token)) {
            return $this->generateJsonErrorResponse(400, ["Auth token not valid"]);
        }

        $user_repository = $this->getDoctrine()->getRepository(User::class);
        $entity_manager = $this->getDoctrine()->getManager();

        $current_user = $user_repository->find($decoded_token->user->id);

        if (! isset($current_user)) {
            return $this->generateJsonErrorResponse(400, ["Problem retrieving current user"]);
        }

        $params = $request->request->all();

        $constraints = new Assert\Collection([
            "url" => [new Assert\NotBlank(), new Assert\Url()],
            "title" => [new Assert\Length(["min" => 10, "allowEmptyString" => false])],
            "description" => [new Assert\Length(["min" => 10, "allowEmptyString" => false])],
        ]);

        $errors = $validator->validate($params, $constraints);

        if (count($errors)) {
            return $this->generateJsonErrorResponse(400, (array) $errors);
        }

        return $this->json([
            "method" => "video.create",
            "params" => $params,
        ], 200);
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
