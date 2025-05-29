<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

#[Route('/auth', name: 'api_auth_')]
final class AuthController extends AbstractController
{
    private UserProviderInterface $userProvider;
    private UserPasswordHasherInterface $passwordHasher;
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(
        UserProviderInterface $userProvider,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
    ) {
        $this->userProvider = $userProvider;
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return $this->json(['error' => 'Email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            /** @var User $user */
            $user = $this->userProvider->loadUserByIdentifier($email);

            if (!$this->passwordHasher->isPasswordValid($user, $password)) {
                throw new AuthenticationException('Invalid credentials.');
            }

            $token = $this->jwtManager->create($user);

            return $this->json([
                'token' => $token,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername(),
                    'slug' => $user->getSlug(),
                ],
            ], Response::HTTP_OK);
        } catch (AuthenticationException $e) {
            return $this->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return $this->json(['error' => 'An error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $username = $data['username'] ?? null;

        if (!$email || !$password || !$username) {
            return $this->json(['error' => 'Missing fields'], Response::HTTP_BAD_REQUEST);
        }

        if ($userRepository->findOneBy(['email' => $email])) {
            return $this->json(['error' => 'Email already used'], Response::HTTP_CONFLICT);
        }

        if ($userRepository->findOneBy(['username' => $username])) {
            return $this->json(['error' => 'Username already used'], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setSlug('@'.strtolower($username));
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $em->persist($user);
        $em->flush();

        $token = $this->jwtManager->create($user);

        return $this->json([
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'slug' => $user->getSlug(),
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'slug' => $user->getSlug(),
        ]);
    }
}
