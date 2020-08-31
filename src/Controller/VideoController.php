<?php

namespace App\Controller;

use App\Entity\Video;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

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
}
