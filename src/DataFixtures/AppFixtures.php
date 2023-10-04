<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    )
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        $user1 = new User();
        $user1->setEmail('test@test.com');
        $user1->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user1,
                '12345678'
            )
        );
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('worker@test.com');
        $user2->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user2,
                '12345678'
            )
        );
        $manager->persist($user2);

        $user3 = new User();
        $user3->setEmail('admin@test.com');
        $user3->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user3,
                '12345678'
            )
        );
        $manager->persist($user3);

         $product1 = new Product();
         $product1->setTitle("Klocki hamulcowe");
         $product1->setPrice(299.99);
         $product1->setCategory("Hamulce");
         $product1->setDescription("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce varius lacus massa, vel cursus ipsum aliquam non. Donec venenatis velit eu erat lacinia dignissim ac in purus. Aenean dui neque, rutrum quis rutrum vel, commodo at metus. Nam quis dui vel tortor rutrum fringilla.");
         $manager->persist($product1);
         $product3 = new Product();
         $product3->setTitle("Filtr kabinowy");
         $product3->setPrice(49.99);
         $product3->setCategory("Filtry");
         $product3->setDescription("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce varius lacus massa, vel cursus ipsum aliquam non. Donec venenatis velit eu erat lacinia dignissim ac in purus. Aenean dui neque, rutrum quis rutrum vel, commodo at metus. Nam quis dui vel tortor rutrum fringilla.");
         $manager->persist($product3);
         $product2 = new Product();
         $product2->setTitle("Tarcze hamulcowe");
         $product2->setPrice(199.99);
         $product2->setCategory("Hamulce");
         $product2->setDescription("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce varius lacus massa, vel cursus ipsum aliquam non. Donec venenatis velit eu erat lacinia dignissim ac in purus. Aenean dui neque, rutrum quis rutrum vel, commodo at metus. Nam quis dui vel tortor rutrum fringilla.");
         $manager->persist($product2);


        $manager->flush();
    }
}
