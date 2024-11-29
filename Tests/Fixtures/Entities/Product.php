<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle\Tests\Fixtures\Entities;

class Product
{
    public function __construct(private readonly string $name, private readonly float $price)
    {
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getPrice(): float
    {
        return $this->price;
    }
}
