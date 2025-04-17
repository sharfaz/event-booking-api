<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\AttendeeRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: AttendeeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'attendees')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_ATTENDEE_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'])]
#[ApiResource(
    operations: [
        new Post(),
        new Get(),
        new Patch()
    ],
    normalizationContext: [
        'groups' => ['attendee:read'],
        'datetime_format' => 'Y-m-d'
    ],
    denormalizationContext: ['groups' => ['attendee:write']],

)]
class Attendee
{
    use TimeStampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['attendee:read', 'event_booking:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['attendee:read', 'attendee:write', 'event_booking:read'])]
    #[NotBlank]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Groups(['attendee:read', 'attendee:write', 'event_booking:read'])]
    #[NotBlank]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['attendee:read', 'attendee:write', 'event_booking:read'])]
    #[NotBlank]
    #[Email]
    private ?string $email = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['attendee:read', 'attendee:write'])]
    #[Type(type: DateTimeInterface::class)]
    #[Assert\LessThan('today', message: 'The date of birth must be in the past.')]
    #[ApiProperty(
        openapiContext: [
            'type' => 'string',
            'format' => 'date',
            'example' => '2023-04-16'
        ]
    )]
    private ?DateTimeInterface $dateOfBirth = null;

    /**
     * @var Collection<int, EventBooking>
     */
    #[ORM\OneToMany(targetEntity: EventBooking::class, mappedBy: 'attendee')]
    #[Groups(['attendee:read'])]
    private Collection $eventBookings;

    public function __construct()
    {
        $this->eventBookings = new ArrayCollection();
    }

    #[Assert\Callback]
    public function validateAge(ExecutionContextInterface $context, mixed $payload): void
    {
        if ($this->getDateOfBirth()) {
            $today = new \DateTime();
            $age = $today->diff($this->dateOfBirth)->y;

            if ($age < 18) {
                $context->buildViolation('You must be at least 18 years old.')
                    ->atPath('dateOfBirth')
                    ->addViolation();
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getDateOfBirth(): ?DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?DateTimeInterface $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * @return Collection<int, EventBooking>
     */
    public function getEventBookings(): Collection
    {
        return $this->eventBookings;
    }

    public function addEventBooking(EventBooking $eventBooking): static
    {
        if (!$this->eventBookings->contains($eventBooking)) {
            $this->eventBookings->add($eventBooking);
            $eventBooking->setAttendee($this);
        }

        return $this;
    }

    public function removeEventBooking(EventBooking $eventBooking): static
    {
        if ($this->eventBookings->removeElement($eventBooking)) {
            // set the owning side to null (unless already changed)
            if ($eventBooking->getAttendee() === $this) {
                $eventBooking->setAttendee(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }
}
