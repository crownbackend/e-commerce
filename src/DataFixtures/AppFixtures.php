<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 30; $i++) {
            $user = new User();
            $password = $this->encoder->encodePassword($user, '123456');
            $user->setEmail($faker->email)
            ->setEnabled($faker->boolean)
            ->setRoles(['ROLE_USER'])
            ->setCreatedAt($faker->dateTime)
            ->setAddress($faker->address)
            ->setCity($faker->city)
            ->setTelephone($faker->phoneNumber)
            ->setLastLogin($faker->dateTime)
            ->setLastName($faker->lastName)
            ->setFirstName($faker->firstName)
            ->setPassword($password);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
