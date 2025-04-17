<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Event;
use App\Factory\EventFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class EventsAPIResourceTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    public function testEventsGetCollection(): void
    {
        EventFactory::createMany(100);

        $response = static::createClient()->request('GET', '/api/events');

        $this->assertResponseIsSuccessful();
        // Asserts that the returned content type is JSON-LD (the default)
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        // Asserts that the returned JSON is a superset of this one
        $this->assertJsonContains([
            '@context' => '/api/contexts/Event',
            '@id' => '/api/events',
            '@type' => 'Collection',
            'totalItems' => 100,
            'view' => [
                '@id' => '/api/events?page=1',
                '@type' => 'PartialCollectionView',
                'first' => '/api/events?page=1',
                'last' => '/api/events?page=4',
                'next' => '/api/events?page=2',
            ],
        ]);

        $this->assertCount(30, $response->toArray()['member']);

        // Asserts that the returned JSON is validated by the JSON Schema generated for this resource by API Platform
        // This generated JSON Schema is also used in the OpenAPI spec!
        $this->assertMatchesResourceCollectionJsonSchema(Event::class);
    }

    public function testCreateEvent(): void
    {
        $response = static::createClient()->request('POST', '/api/events', [
            'json' => [
                'name' => 'BBC Charity Concert',
                'description' => 'A charity concert organized by the BBC.',
                'eventDate' => '2026-04-16 12:00:00',
                'capacity' => 100,
                'country' => 'United Kingdom',
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/Event',
            '@type' => 'Event',
            'name' => 'BBC Charity Concert',
            'description' => 'A charity concert organized by the BBC.',
            'eventDate' => '2026-04-16 12:00:00',
            'capacity' => 100,
            'country' => 'United Kingdom',
            'eventBookings' => [],
        ]);
        $this->assertMatchesRegularExpression('~^/api/events/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Event::class);
    }

    public function testCreateInvalidEvent(): void
    {
        static::createClient()->request('POST', '/api/events', ['json' => [
            'description' => 'A charity concert organized by the BBC.',
            'eventDate' => '2026-04-16 12:00:00',
            'capacity' => 100,
            'country' => 'United Kingdom',
        ]]);

        $this->assertResponseStatusCodeSame(422);

        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolation',
            '@type' => 'ConstraintViolation',
            'status' => 422,
            'description' => 'name: This value should not be blank.',
        ]);
    }

    public function testUpdateEvent(): void
    {
        $event = ['name' => 'BBC Charity Concert'];
        EventFactory::createOne($event);

        $client = static::createClient();
        // findIriBy allows to retrieve the IRI of an item by searching for some of its properties.
        $iri = $this->findIriBy(Event::class, $event);

        // Use the PATCH method here to do a partial update
        $client->request('PATCH', $iri, [
            'json' => [
                'description' => 'BBC Charity Concert - Updated',
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'name' => 'BBC Charity Concert',
            'description' => 'BBC Charity Concert - Updated',
        ]);
    }

    public function testDeleteEvent(): void
    {
        $event = ['name' => 'BBC Charity Concert'];
        EventFactory::createOne($event);

        $client = static::createClient();
        $iri = $this->findIriBy(Event::class, $event);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Event::class)->findOneBy($event)
        );
    }
}
