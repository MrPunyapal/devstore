<?php

namespace Database\Factories;

use Database\Seeders\ProductCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $row = fake()->randomElement(ProductCatalog::rows());
        $row['name'] .= ' · '.fake()->unique()->bothify('SKU-??####');

        return $row;
    }
}
