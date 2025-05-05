<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Create 1 user to be followed
        $firstuser = new User();
        $firstuser->setEmail('user@user.com');
        $firstuser->setUsername('user');
        $firstuser->setPassword($this->hasher->hashPassword($firstuser, 'password'));
        $firstuser->setSlug('@user');
        $manager->persist($firstuser);

        $this->addReference('user_0', $firstuser);

        // Create 10 other users
        for ($i = 1; $i <= 10; ++$i) {
            $username = $faker->unique()->userName();
            $user = new User();
            $user->setEmail($faker->unique()->email());
            $user->setUsername($username);
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
            $user->setSlug("@$username");
            $user->setDescription($faker->text());
            $user->addFollow($firstuser);
            $manager->persist($user);

            $this->addReference("user-$i", $user);
        }

        $manager->flush();
    }
}
