<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Video;
use App\Entity\VideoReaction;
use App\Enum\ReactionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class VideoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $this->loadVideos($manager, $faker);
        $this->loadTags($manager, $faker);
        $this->loadReactions($manager);
    }

    public function loadVideos(ObjectManager $manager, Generator $faker): void
    {
        for ($i = 1; $i <= 10; ++$i) {
            $video = new Video();
            /** @var User $user */
            $user = $this->getReference("user-$i", User::class);

            $user->addvideo($video);

            $video->setTitle($faker->sentence());
            $video->setDescription($faker->text());
            $video->incrementViews();
            $manager->persist($video);

            $manager->flush();

            $this->addReference("video-$i", $video);
        }
    }

    public function loadTags(ObjectManager $manager, Generator $faker): void
    {
        for ($i = 1; $i <= 10; ++$i) {
            $tag = new Tag();
            /** @var Video $video */
            $video = $this->getReference("video-$i", Video::class);

            $video->addtag($tag);

            $tag->setName($faker->word());
            $manager->persist($tag);

            $manager->flush();
        }
    }

    public function loadReactions(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; ++$i) {
            $reaction = new VideoReaction();
            /** @var Video $video */
            $video = $this->getReference("video-$i", Video::class);
            /** @var User $user */
            $user = $this->getReference("user-$i", User::class);

            $video->addReaction($reaction);
            $user->addReaction($reaction);

            $reaction->setType(ReactionType::LIKE);
            $manager->persist($reaction);

            $manager->flush();
        }
    }

    /**
     * @return array<class-string<Fixture>>
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
