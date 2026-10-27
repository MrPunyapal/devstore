<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, Searchable;

    public const CATEGORIES = ['laptops', 'monitors', 'keyboards', 'mice', 'headphones', 'webcams', 'docking stations'];

    protected $guarded = [];

    protected function casts(): array
    {
        return ['price' => 'float', 'ram_gb' => 'integer', 'storage_gb' => 'integer', 'screen_size' => 'float', 'weight_kg' => 'float', 'rating' => 'float', 'in_stock' => 'boolean', 'wireless' => 'boolean'];
    }

    public function searchableAs(): string
    {
        return 'products';
    }

    public function toSearchableArray(): array
    {
        return array_filter(['id' => (string) $this->id, ...$this->only(['name', 'brand', 'category', 'description', 'price', 'ram_gb', 'storage_gb', 'processor', 'screen_size', 'weight_kg', 'rating', 'in_stock', 'wireless']), 'created_at' => $this->created_at->timestamp], fn ($value) => $value !== null);
    }
}
