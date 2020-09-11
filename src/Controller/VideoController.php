<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Video;
use App\Repository\VideoRepository;
use App\Service\JwtAuth;
use Cassandra\Exception\WriteTimeoutException;
use Container7n28FQI\getSecurity_Firewall_Map_Context_DevService;
use Doctrine\ORM\Query\Expr\Math;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class VideoController extends AbstractController
{
    /**
     * @Route("/video/create", methods={"POST"}, name="video.create")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \App\Service\JwtAuth $auth
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function create(Request $request, ValidatorInterface $validator, JwtAuth $auth)
    {
        $request = $this->processRequest($request);
        $loginAttempt = $this->getLoggedUser($request->headers->get("Authorization", ""), $auth);
        if ($loginAttempt instanceof JsonResponse) {
            return $loginAttempt;
        }
        $current_user = $loginAttempt;

        $entity_manager = $this->getDoctrine()->getManager();

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

        $new_video = new Video();

        $new_video->setTitle($params["title"]);
        $new_video->setDescription($params["description"]);
        $new_video->setUrl($params["url"]);
        $new_video->setUser($current_user);

        if (! $this->existsInArray($new_video, $current_user->getVideos())) {
            $entity_manager->persist($new_video);
            $current_user->addVideo($new_video);

            $entity_manager->flush();

            return $this->json([
                "code" => 200,
                "status" => 1,
                "videos" => $current_user->getVideos()->map(function (Video $video) {
                    $res = [];
                    $res['id'] = $video->getId();
                    $res['title'] = $video->getTitle();
                    $res['description'] = $video->getDescription();
                    $res['url'] = $video->getUrl();

                    return $res;
                }),
                "video_added" => $new_video,
            ], 200);
        }

        return $this->generateJsonErrorResponse(400, [
            "Video already exists",
            [
                "videos" => $current_user->getVideos()->map(function (Video $video) {
                    $res = [];
                    $res['id'] = $video->getId();
                    $res['title'] = $video->getTitle();
                    $res['description'] = $video->getDescription();
                    $res['url'] = $video->getUrl();

                    return $res;
                }),
            ],
        ]);
    }

    /**
     * @Route("/videos", methods={"GET"}, name="video.videos")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Knp\Component\Pager\PaginatorInterface $pager
     * @param \App\Service\JwtAuth $auth
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getVideosPaginated(Request $request, PaginatorInterface $pager, JwtAuth $auth)
    {
        $request = $this->processRequest($request);
        $loginAttempt = $this->getLoggedUser($request->headers->get("Authorization"), $auth);
        if ($loginAttempt instanceof JsonResponse) {
            return $loginAttempt;
        }
        $current_user = $loginAttempt;

        $entity_manager = $this->getDoctrine()->getManager();

        //Empezamos con  la paginación de los videos. Hay que recordar que el paginador usa sentencias en DQL. Tenemos simplemente que recoger los videos del usuario registrado
        $dql_query = "SELECT v FROM App\Entity\Video v WHERE v.user = {$current_user->getId()} ORDER BY v.id DESC";
        $query = $entity_manager->createQuery($dql_query);

        //Recogemos la página que queremos consultar de la query string. Por defecto, si no viene ninguna, es la primera
        $page = $request->query->get("page", 1);
        $items_per_page = 5;

        //Paginamos los resultados de la consulta que hicimos
        $pagination = $pager->paginate($query, $page, $items_per_page);

        return $this->json([
            "code" => 200,
            "status" => 1,
            "total_videos" => $pagination->getTotalItemCount(),
            "items_per_page" => $pagination->getItemNumberPerPage(),
            "current_page" => $pagination->getCurrentPageNumber(),
            "total_pages" => ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage()),
            "videos" => $pagination,
            "user_id" => $current_user->getId(),
        ], 200);
    }

    /**
     * @Route("/videos/{id}", methods={"GET"}, name="video.video")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Service\JwtAuth $auth
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getVideoInfo(Request $request, JwtAuth $auth, $id)
    {
        $request = $this->processRequest($request);
        return $this->json($request->headers->all());
        $loginAttempt = $this->getLoggedUser($request->headers->get("Authorization"), $auth);
        if ($loginAttempt instanceof JsonResponse) {
            return $loginAttempt;
        }
        $current_user = $loginAttempt;

        $video_repository = $this->getDoctrine()->getRepository(Video::class);

        $video = $video_repository->findOneBy(["id" => $id, "user" => $current_user->getId()]);

        if (! isset($video)) {
            return $this->generateJsonErrorResponse(404, ["Video not found in favourites"]);
        }

        return $this->json([
            "status" => 1,
            "code" => 200,
            "video" => $video,
        ]);
    }

    /**
     * @Route("/videos/{id}", methods={"DELETE"}, name="video.remove")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Service\JwtAuth $auth
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function removeVideo(Request $request, JwtAuth $auth, $id)
    {
        $request = $this->processRequest($request);
        $loginAttempt = $this->getLoggedUser($request->headers->get("Authorization"), $auth);
        if ($loginAttempt instanceof JsonResponse) {
            return $loginAttempt;
        }
        $current_user = $loginAttempt;

        $video_repository = $this->getDoctrine()->getRepository(Video::class);

        $video = $video_repository->findOneBy(["id" => $id, "user" => $current_user->getId()]);

        if (! isset($video)) {
            return $this->generateJsonErrorResponse(404, ["Video not found in favourites"]);
        }

        $entity_manager = $this->getDoctrine()->getManager();
        $entity_manager->remove($video);
        $entity_manager->flush();

        return $this->json([
            "status" => 1,
            "code" => 200,
            "video" => $video,
        ]);
    }

    /**
     * @Route("/videos/{id}", methods={"PUT", "UPDATE"}, name="video.update")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Service\JwtAuth $auth
     * @param $id
     * @return object|\Symfony\Component\HttpFoundation\JsonResponse|null
     */
    public function updateVideo(Request $request, JwtAuth $auth, $id)
    {
        $request = $this->processRequest($request);

        $loginAttempt = $this->getLoggedUser($request->headers->get("Authorization"), $auth);
        if ($loginAttempt instanceof JsonResponse) {
            return $loginAttempt;
        }
        $current_user = $loginAttempt;

        $video_repository = $this->getDoctrine()->getRepository(Video::class);

        $video = $video_repository->findOneBy(["id" => $id, "user" => $current_user->getId()]);

        if (! isset($video)) {
            return $this->generateJsonErrorResponse(404, ["Video not found in favourites"]);
        }

        $validator = Validation::createValidator();
        $params = $request->request->all();
        $constraints = new Assert\Collection([
            "title"       => new Assert\Optional(new Assert\Length(["min" => 5])),
            "description" => new Assert\Optional(new Assert\Length(["min" => 10])),
            "url"         => new Assert\Optional(new Assert\Url()),
        ]);
        $errors = $validator->validate($params, $constraints);

        if ($errors->count() > 0) {
            return $this->generateJsonErrorResponse(400, (array) $errors);
        }

        //Recorremos todos los parámetros que queremos cambiar que están en la request y llamamos al setter de cada uno
        foreach ($params as $key => $value) {
            call_user_func_array([$video, 'set'.ucfirst($key)], [$value]);
        }

        $entity_manager = $this->getDoctrine()->getManager();

        $entity_manager->flush();

        return $this->json([
            "status" => 1,
            "code"   => 200,
            "video"  => $video,
        ]);
    }

    /**
     * @param string $auth_token
     * @param \App\Service\JwtAuth $auth
     * @return object|\Symfony\Component\HttpFoundation\JsonResponse|null
     */
    private function getLoggedUser(string $auth_token, JwtAuth $auth)
    {
        if (! isset($auth_token)) {
            return $this->generateJsonErrorResponse(400, ["Auth token missing"]);
        }

        $decoded_token = $auth->isTokenValid($auth_token);

        if (! isset($decoded_token)) {
            return $this->generateJsonErrorResponse(400, ["Auth token not valid"]);
        }

        $user_repository = $this->getDoctrine()->getRepository(User::class);

        $current_user = $user_repository->find($decoded_token->user->id);

        if (! isset($current_user)) {
            return $this->generateJsonErrorResponse(400, ["Problem retrieving current user"]);
        }

        return $current_user;
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

    /**
     * @param Video $entry
     * @param Video[] $array
     * @return bool
     */
    private function existsInArray($entry, $array)
    {
        foreach ($array as $compare) {
            if ($compare->getUrl() == $entry->getUrl()) {
                return true;
            }
        }
        return false;
    }
    private function processRequest(Request $request){
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }
        return $request;
    }
}
