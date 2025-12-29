<?php

namespace App\Entity;

class House
{
    public function __construct(
        public int $id,
        public string $name,
        public int $beds,
        public string $amenities,
        public int $distanceToSea,
        public float $pricePerNight
    ) {}

    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'beds' => $this->beds,
            'amenities' => $this->amenities,
            'distance_to_sea' => $this->distanceToSea,
            'price_per_night' => $this->pricePerNight
        ];
    }
}
