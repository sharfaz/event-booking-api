<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\EventRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'events')]
#[ApiResource(
    //This is an example of how to use with Authenticated resources
    //Only authenticated users can access the API specific operations
    /*operations: [
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new GetCollection(),
        new Get()
    ]*/
    operations: [
        new Post(),
        new Delete(),
        new Patch(),
        new GetCollection(),
        new Get()
    ],
    normalizationContext: [
        'groups' => ['event:read'],
        'datetime_format' => 'Y-m-d H:i:s'
    ],
    denormalizationContext: ['groups' => ['event:write']]
)]
class Event
{
    use TimeStampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['event:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['event:read', 'event:write'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['event:read', 'event:write'])]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['event:read', 'event:write'])]
    #[Assert\NotBlank]
    #[Assert\Type(type: DateTimeInterface::class)]
    #[ApiProperty(
        openapiContext: [
            'type' => 'string',
            'format' => 'date',
            'example' => '2023-04-16 12:00:00'
        ]
    )]
    private ?DateTimeInterface $eventDate = null;

    #[ORM\Column]
    #[Groups(['event:read', 'event:write'])]
    #[Assert\NotBlank]
    private ?int $capacity = null;

    #[ORM\Column(length: 100)]
    #[Groups(['event:read', 'event:write'])]
    #[Assert\NotBlank]
    private ?string $country = null;

    /**
     * @var Collection<int, EventBooking>
     */
    #[ORM\OneToMany(targetEntity: EventBooking::class, mappedBy: 'event')]
    #[Groups(['event:read'])]
    private Collection $eventBookings;

    public function __construct()
    {
        $this->eventBookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getEventDate(): ?DateTimeInterface
    {
        return $this->eventDate;
    }

    public function setEventDate(DateTimeInterface $eventDate): static
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

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
            $eventBooking->setEvent($this);
        }

        return $this;
    }

    public function removeEventBooking(EventBooking $eventBooking): static
    {
        if ($this->eventBookings->removeElement($eventBooking)) {
            // set the owning side to null (unless already changed)
            if ($eventBooking->getEvent() === $this) {
                $eventBooking->setEvent(null);
            }
        }

        return $this;
    }
}
