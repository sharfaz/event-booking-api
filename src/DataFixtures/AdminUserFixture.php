<?php

namespace App\DataFixtures;

use App\Entity\AdminUser;
use App\Factory\AdminUserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AdminUserFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        AdminUserFactory::createSequence(function () {
            foreach (range(1, 10) as $i) {
                yield ['email' => 'admin' . $i . '@example.com'];
            }
        });
    }
}
