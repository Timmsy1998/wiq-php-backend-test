<?php
namespace Wiq\Model;

/**
 * Basic product data holder.
 * Includes a small helper to convert itself back into an array
 * for use in PUT requests or serialization.
 */
class Product
{
    public function __construct(
        public int $id,
        public string $name,
        public ?float $price = null,
    ) {}

    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "price" => $this->price,
        ];
    }
}
