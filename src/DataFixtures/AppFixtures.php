<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{


    private Generator $faker;
    
 
    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
       
    }
   

    public function load(ObjectManager $manager): void
    {
        //ingrédients
        for ($i=1; $i <=50 ; $i++) { 
            $ingredient = new Ingredient();
            $ingredient ->setName($this->faker->word())
                      ->setPrice(mt_rand(0, 100));
            $ingredients[]= $ingredient;
            $manager->persist($ingredient);
        }
        //Recipes
        for ($i=0; $i <10; $i++) { 
            $recipe = new Recipe();
            $recipe->setName($this->faker->word())
                ->setTime(mt_rand(0, 1) == 1 ? mt_rand(1, 1440) : null)
                ->setTime(mt_rand(0, 1) == 1 ? mt_rand(1, 50) : null)
                ->setDifficulty(mt_rand(0, 1) == 1 ? mt_rand(1, 5) : null)
                ->setDescription($this->faker->text(300))
                ->setPrice(mt_rand(0, 1) == 1 ? mt_rand(1, 1000) : null)
                ->setIsFavorite(mt_rand(0, 1) == 1 ? true : false);
            for ($i=0; $i < mt_rand(5, 15) ; $i++) { 
                $recipe->addIngredient($ingredients[mt_rand(0, count($ingredients) - 1)]);
            }
            $manager->persist($recipe);
            }
        // users
        for ($i=0; $i <10; $i++) { 
            $user = new User();
            $user->setfullName($this->faker->name())
                ->setPseudo(mt_rand(0, 1) === 1 ? $this->faker->firstName() : null)
                ->setEmail($this->faker->email())
                ->setRoles(['ROLE_USER'])
                ->setPlainPassword('password');

                $manager->persist($user);
        }
       $manager->flush();
    }
   
    

}
