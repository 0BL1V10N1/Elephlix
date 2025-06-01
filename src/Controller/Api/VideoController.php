<?php

namespace App\Controller\Api;

use App\Entity\Comment;
use App\Entity\User;
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

        $this->em->flush();

        return $this->json($video, Response::HTTP_OK, [], ['groups' => ['video:detail']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $title = $request->request->get('title', '');
        $description = $request->request->get('description', '');
        $uploadedFile = $request->files->get('file');
        $thumbnail = $request->files->get('thumbnail');

        $video = new Video();
        $video->setTitle($title);
        $video->setDescription($description);
        $video->setVideoFile($uploadedFile);
        $video->setAuthor($this->getUser());

        if ($thumbnail) {
            $video->setThumbnail($thumbnail);
        }

        // Validation
        $errors = $this->validator->validate($video);
        if (\count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(['errors' => $errorsArray], Response::HTTP_BAD_REQUEST);
        }

        $targetDir = $this->getParameter('uploads_directory').'/'.$video->getSlug();
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        try {
            $uploadedFile->move($targetDir, 'video.mp4');
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to upload video'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($thumbnail) {
            $mime = $thumbnail->getClientMimeType();

            switch ($mime) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($thumbnail->getPathname());
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($thumbnail->getPathname());
                    break;
                case 'image/webp':
                    $image = imagecreatefromwebp($thumbnail->getPathname());
                    break;
                default:
                    return $this->json(['error' => 'Unsupported image type for thumbnail'], Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
            }

            if (!$image) {
                return $this->json(['error' => 'Failed to process thumbnail'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            imagejpeg($image, $targetDir.'/thumbnail.jpg', 85);
            imagedestroy($image);
        } else {
            $defaultThumbnail = $this->getParameter('assets_directory').'/default_thumbnail.jpg';
            copy($defaultThumbnail, $targetDir.'/thumbnail.jpg');
        }

        $this->em->persist($video);
        $this->em->flush();

        return $this->json($video, Response::HTTP_CREATED, [], ['groups' => ['video:detail']]);
    }

    #[Route('/{slug}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $slug): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $video = $this->videoRepository->findOneBy(['slug' => $slug]);
        if (!$video) {
            return $this->json(['error' => 'Video not found'], Response::HTTP_NOT_FOUND);
        }

        if ($video->getAuthor() !== $user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $user->removeVideo($video);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{slug}/comments', name: 'comment_create', methods: ['POST'])]
    public function addComment(string $slug, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $video = $this->videoRepository->findOneBy(['slug' => $slug]);
        if (!$video) {
            return $this->json(['error' => 'Video not found'], Response::HTTP_NOT_FOUND);
        }

        $content = $request->request->get('content', '');

        $comment = new Comment();
        $comment->setContent($content);
        $comment->setAuthor($user);
        $comment->setVideo($video);

        // Validation
        $errors = $this->validator->validate($comment);
        if (\count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(['errors' => $errorsArray], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($comment);
        $this->em->flush();

        return $this->json($comment, Response::HTTP_CREATED, [], ['groups' => ['video:detail']]);
    }
}
