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
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
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
     * @param \App\Service\JwtAuth $auth
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getVideosPaginated(Request $request, PaginatorInterface $pager, JwtAuth $auth)
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

        //empezamos con  la paginación de los videos. Hay que recordar que el paginador usa sentencias en DQL
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

            return false;
        }
    }
}
