<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\EventBooking;
use App\Factory\AttendeeFactory;
use App\Factory\EventBookingFactory;
use App\Factory\EventFactory;
use App\Story\EventBookingStory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class EventBookingAPIResourceTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    public function testEventBookingsGetCollection(): void
    {
        EventBookingStory::load();

        $response = static::createClient()->request('GET', '/api/event-bookings');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/EventBooking',
            '@id' => '/api/event-bookings',
            '@type' => 'Collection',
        ]);
        $this->assertCount(10, $response->toArray()['member']);
        $this->assertMatchesResourceCollectionJsonSchema(EventBooking::class);
    }

    public function testCreateEventBooking(): void
    {
        $event = EventFactory::createOne();
        $attendee = AttendeeFactory::createOne();

        $response = static::createClient()->request('POST', '/api/event-bookings', [
            'json' => [
                'event' => '/api/events/'.$event->getId(),
                'attendee' => '/api/attendees/'.$attendee->getId(),
                'bookingDate' => '2026-04-16 12:00:00',
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@type' => 'EventBooking',
        ]);
        $this->assertMatchesResourceItemJsonSchema(EventBooking::class);
    }

    public function testDeleteEventBooking(): void
    {
        $eventBooking = ['bookingDate' => \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2026-04-16 12:00:00')];
        EventBookingFactory::createOne($eventBooking);

        $client = static::createClient();
        $iri = $this->findIriBy(EventBooking::class, $eventBooking);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(EventBooking::class)->findOneBy($eventBooking)
        );
    }
}
