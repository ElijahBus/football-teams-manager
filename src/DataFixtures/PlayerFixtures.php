<?php

namespace App\DataFixtures;

use App\Entity\Player;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PlayerFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Each sub array holds the name & surname of the player
        $players = [
            ["Ben", "Julius"],
            ["Martin", "Fowler"],
            ["Jules", "Franc"],
            ["Jacques", "Benz"],
            ["Ceph", "Carl"],
            ["Mark", "Luke"],
            ["Jean", "DuBois"],
            ["Paul", "Baker"],
            ["Jeanne", "Ciel"],
            ["Star", "Francis"],
            ["Lune", "Perez"]
        ];

        foreach ($players as $player) {
            $abstractPlayer = new Player();
            $abstractPlayer->setName($player[0]);
            $abstractPlayer->setSurname($player[1]);

            $manager->persist($abstractPlayer);
        }

        $manager->flush();
    }
}
