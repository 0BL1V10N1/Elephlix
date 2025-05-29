<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/users', name: 'api_users_')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(string $slug): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['slug' => $slug]);

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($user, Response::HTTP_OK, [], ['groups' => ['user:show']]);
    }

    #[Route('/{slug}/videos', name: 'videos', methods: ['GET'])]
    public function videos(string $slug): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['slug' => $slug]);

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $videos = $user->getVideos();

        return $this->json($videos, Response::HTTP_OK, [], ['groups' => ['video:list']]);
    }
}
