<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\HouseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\HouseController;

#[ORM\Entity(repositoryClass: HouseRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/houses',
            controller: HouseController::class . '::createHouse',
            description: 'Create new house',
            security: 'is_granted("IS_AUTHENTICATED_FULLY")'
        ),
        new GetCollection(
            uriTemplate: '/houses/available',
            controller: HouseController::class . '::getAvailableHouses',
            description: 'Get all available houses',
            security: 'is_granted("IS_AUTHENTICATED_FULLY")'
        ),
        new Get(
            uriTemplate: '/houses/{id}',
            controller: HouseController::class . '::getHouse',
            description: 'Get house by ID',
            security: 'is_granted("IS_AUTHENTICATED_FULLY")'
        ),
        new Delete(
            uriTemplate: '/houses/{id}',
            controller: HouseController::class . '::deleteHouse',
            description: 'Delete house',
            security: 'is_granted("IS_AUTHENTICATED_FULLY")'
        ),
        new Get(
            uriTemplate: '/houses/{id}/bookings',
            controller: HouseController::class . '::getHouseBookings',
            description: 'Get bookings for house',
            security: 'is_granted("IS_AUTHENTICATED_FULLY")'
        ),
    ]
)]
class House
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $beds = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $amenities = null;

    #[ORM\Column(name: 'distance_to_sea')]
    private ?int $distanceToSea = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $pricePerNight = null;

    #[ORM\Column]
    private ?bool $isAvailable = true;

    #[ORM\OneToMany(mappedBy: 'house', targetEntity: Booking::class, cascade: ['remove'])]
    private Collection $bookings;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
    }

    // Геттеры и сеттеры
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

    public function getBeds(): ?int
    {
        return $this->beds;
    }

    public function setBeds(int $beds): static
    {
        $this->beds = $beds;

        return $this;
    }

    public function getAmenities(): ?string
    {
        return $this->amenities;
    }

    public function setAmenities(?string $amenities): static
    {
        $this->amenities = $amenities;

        return $this;
    }

    public function getDistanceToSea(): ?int
    {
        return $this->distanceToSea;
    }

    public function setDistanceToSea(int $distanceToSea): static
    {
        $this->distanceToSea = $distanceToSea;

        return $this;
    }

    public function getPricePerNight(): ?string
    {
        return $this->pricePerNight;
    }

    public function setPricePerNight(string $pricePerNight): static
    {
        $this->pricePerNight = $pricePerNight;

        return $this;
    }

    public function isIsAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setHouse($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getHouse() === $this) {
                $booking->setHouse(null);
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'beds' => $this->beds,
            'amenities' => $this->amenities,
            'distance_to_sea' => $this->distanceToSea,
            'price_per_night' => $this->pricePerNight,
            'is_available' => $this->isAvailable,
        ];
    }
}
