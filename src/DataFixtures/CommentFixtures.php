<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 1; $i <= 10; ++$i) {
            $comment = new Comment();
            /** @var Video $video */
            $video = $this->getReference("video-$i", Video::class);
            /** @var User $user */
            $user = $this->getReference("user-$i", User::class);

            $comment->setContent($faker->realText());
            $comment->setVideo($video);
            $comment->setAuthor($user);
            $video->addComment($comment);
            $manager->persist($comment);
        }

        $manager->flush();
    }

    /**
     * @return array<class-string<Fixture>>
     */
    public function getDependencies(): array
    {
        return [VideoFixtures::class];
    }
}
