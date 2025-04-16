<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\EventBookingRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: EventBookingRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_EVENT_BOOKINGS', fields: ['event', 'attendee'])]
#[UniqueEntity(fields: ['event', 'attendee'], message: 'There is already a booking for this event by this attendee.')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'event_bookings')]
#[ApiResource(
    operations: [
        new Post(),
        new Delete(),
        new GetCollection(),
        new Get()
    ],
    normalizationContext: [
        'groups' => ['event_booking:read'],
        'datetime_format' => 'Y-m-d H:i:s'
    ],
    denormalizationContext: ['groups' => ['event_booking:write']]
)]
class EventBooking
{
    use TimeStampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['event_booking:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'eventBookings')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups(['event_booking:read', 'event_booking:write'])]
    #[Assert\NotBlank]
    #[Assert\Valid]
    private ?Event $event = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'eventBookings')]
    #[Groups(['event_booking:read', 'event_booking:write'])]
    #[Assert\NotBlank]
    #[Assert\Valid]
    private ?Attendee $attendee = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual('today', message: 'The booking date must be today or in the future.')]
    #[Assert\Type(type: DateTimeInterface::class)]
    #[Groups(['event_booking:read', 'event_booking:write'])]
    #[ApiProperty(
        openapiContext: [
            'type' => 'string',
            'format' => 'date',
            'example' => '2023-04-16 12:00:00'
        ]
    )]
    private ?DateTimeInterface $bookingDate = null;

    #[Assert\Callback]
    public function validateCapacity(ExecutionContextInterface $context, mixed $payload): void
    {
        if ($this->getEvent() !== null) {
            $capacity = $this->getEvent()->getCapacity();
            if ($this->getEvent()->getEventBookings()->count() >= $capacity) {
                $context->buildViolation('There is no available space for the event. The event is fully booked.')
                    ->atPath('event')
                    ->addViolation();
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): void
    {
        $this->event = $event;
    }

    public function getAttendee(): ?Attendee
    {
        return $this->attendee;
    }

    public function setAttendee(?Attendee $attendee): void
    {
        $this->attendee = $attendee;
    }

    public function getBookingDate(): ?DateTimeInterface
    {
        return $this->bookingDate;
    }

    public function setBookingDate(DateTimeInterface $bookingDate): static
    {
        $this->bookingDate = $bookingDate;

        return $this;
    }
}
