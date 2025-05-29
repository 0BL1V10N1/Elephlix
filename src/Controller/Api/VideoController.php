<?php

namespace App\Controller\Api;

use App\Entity\Video;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/videos', name: 'api_videos_')]
final class VideoController extends AbstractController
{
    public function __construct(
        private readonly VideoRepository $videoRepository,
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $videos = $this->videoRepository->findAll();

        return $this->json($videos, Response::HTTP_OK, [], ['groups' => ['video:list']]);
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(string $slug): JsonResponse
    {
        $video = $this->videoRepository->findOneBy(['slug' => $slug]);
        if (!$video) {
            return $this->json(['error' => 'Video not found'], Response::HTTP_NOT_FOUND);
        }

        // Increment views when loading the video page
        $video->incrementViews();

        return $this->json($video, Response::HTTP_OK, [], ['groups' => ['video:detail']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        $video = new Video();
        $video->setTitle($data['title'] ?? '');
        $video->setDescription($data['description'] ?? '');
        $video->setAuthor($this->getUser());

        // Validation
        $errors = $this->validator->validate($video);
        if (\count($errors) > 0) {
            $errorsString = (string) $errors;

            return $this->json(['error' => $errorsString], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($video);
        $this->em->flush();

        return $this->json($video, Response::HTTP_CREATED, [], ['groups' => ['video:detail']]);
    }

    #[Route('/{slug}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $slug): JsonResponse
    {
        $video = $this->videoRepository->findOneBy(['slug' => $slug]);
        if (!$video) {
            return $this->json(['error' => 'Video not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($video);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
