<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Attendee;
use App\Factory\AttendeeFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class AttendeeAPIResourceTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    public function testCreateAttendee(): void
    {
        $response = static::createClient()->request('POST', '/api/attendees', [
            'json' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john.doe@example.com',
                'dateOfBirth' => '1990-01-01',
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'dateOfBirth' => '1990-01-01',
        ]);
        $this->assertMatchesResourceItemJsonSchema(Attendee::class);
    }

    public function testUpdateAttendee(): void
    {
        $attendee = ['email' => 'john.doe@example.com'];
        AttendeeFactory::createOne($attendee);

        $client = static::createClient();
        $iri = $this->findIriBy(Attendee::class, $attendee);

        $client->request('PATCH', $iri, [
            'json' => ['firstName' => 'Johnny'],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'firstName' => 'Johnny',
        ]);
    }
}
