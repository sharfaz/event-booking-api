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

    public function testDuplicateEventBooking(): void
    {
        $event = EventFactory::createOne();
        $attendee = AttendeeFactory::createOne();

        // First booking attempt
        static::createClient()->request('POST', '/api/event-bookings', [
            'json' => [
                'event' => '/api/events/' . $event->getId(),
                'attendee' => '/api/attendees/' . $attendee->getId(),
                'bookingDate' => '2026-04-16 12:00:00',
            ]
        ]);

        // Second booking attempt (duplicate)
        $response = static::createClient()->request('POST', '/api/event-bookings', [
            'json' => [
                'event' => '/api/events/' . $event->getId(),
                'attendee' => '/api/attendees/' . $attendee->getId(),
                'bookingDate' => '2026-04-16 12:00:00',
            ]
        ]);

        // Assert that the response status code is 422 (Unprocessable Entity)
        $this->assertResponseStatusCodeSame(422);

        // Assert that the response contains the validation error message
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolation',
            '@type' => 'ConstraintViolation',
            'violations' => [
                [
                    'propertyPath' => 'event',
                    'message' => 'There is already a booking for this event by this attendee.',
                ]
            ]
        ]);
    }

    public function testEventBookingCapacityExceeded(): void
    {
        $event = EventFactory::createOne(['capacity' => 2]);
        $attendees = AttendeeFactory::createMany(3);

        // Book the event for the first two attendees (within capacity)
        foreach (array_slice($attendees, 0, 2) as $attendee) {
            static::createClient()->request('POST', '/api/event-bookings', [
                'json' => [
                    'event' => '/api/events/' . $event->getId(),
                    'attendee' => '/api/attendees/' . $attendee->getId(),
                    'bookingDate' => '2026-04-16 12:00:00',
                ]
            ]);
        }

        // Attempt to book the event for the third attendee (exceeding capacity)
        $response = static::createClient()->request('POST', '/api/event-bookings', [
            'json' => [
                'event' => '/api/events/' . $event->getId(),
                'attendee' => '/api/attendees/' . $attendees[2]->getId(),
                'bookingDate' => '2026-04-16 12:00:00',
            ]
        ]);

        // Assert that the response status code is 422 (Unprocessable Entity)
        $this->assertResponseStatusCodeSame(422);

        // Assert that the response contains the validation error message
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolation',
            '@type' => 'ConstraintViolation',
            'violations' => [
                [
                    'propertyPath' => 'event',
                    'message' => 'There is no available space for the event. The event is fully booked.',
                ]
            ]
        ]);
    }
}
