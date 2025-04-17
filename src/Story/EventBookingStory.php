<?php

namespace App\Story;

use App\Factory\AttendeeFactory;
use App\Factory\EventBookingFactory;
use App\Factory\EventFactory;
use Zenstruck\Foundry\Story;

final class EventBookingStory extends Story
{
    public function build(): void
    {
        //(https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#stories)
        EventFactory::createMany(100);

        AttendeeFactory::createMany(20);

        EventBookingFactory::createMany(30, function () {
            return [
                'event' => EventFactory::random(),
                'attendee' => AttendeeFactory::random(),
            ];
        });
    }
}
