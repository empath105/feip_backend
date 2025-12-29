<?php

namespace App\Entity;

class Booking
{
    public function __construct(
        public int $id,
        public int $houseId,
        public string $phone,
        public string $comment,
        public string $createdAt
    ) {}

    public function toArray(): array {
        return [
            'id' => $this->id,
            'house_id' => $this->houseId,
            'phone' => $this->phone,
            'comment' => $this->comment,
            'created_at' => $this->createdAt,
            'status' => 'active'
        ];
    }
}
