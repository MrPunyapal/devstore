<?php

use App\Models\Product;
use Database\Seeders\DatabaseSeeder;

it('casts and serializes the product for Typesense', function () {
    $product = Product::factory()->create(['price' => 89999.50, 'ram_gb' => 32, 'storage_gb' => 1024, 'wireless' => false, 'in_stock' => true]);
    $document = $product->toSearchableArray();
    expect($document['id'])->toBeString()->and($document['price'])->toBe(89999.5)->and($document['ram_gb'])->toBe(32)->and($document['in_stock'])->toBeTrue()->and($document['wireless'])->toBeFalse()->and($document['created_at'])->toBeInt();
});
it('omits inapplicable fields without losing false or zero values', function () {
    $product = Product::factory()->create(['category' => 'mice', 'ram_gb' => null, 'storage_gb' => null, 'processor' => null, 'screen_size' => null, 'wireless' => false]);
    expect($product->toSearchableArray())->not->toHaveKeys(['ram_gb', 'storage_gb', 'processor', 'screen_size'])->toHaveKey('wireless', false);
});
it('seeds a varied valid catalog and can be rerun', function () {
    $this->seed(DatabaseSeeder::class);
    expect(Product::count())->toBe(292)->and(Product::distinct()->pluck('category')->sort()->values()->all())->toBe(collect(Product::CATEGORIES)->sort()->values()->all());
    foreach (Product::all() as $product) {
        expect($product->price)->toBeGreaterThan(0)->and($product->rating)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(5)->and($product->weight_kg)->toBeGreaterThan(0)->and($product->name)->not->toMatch('/^Product \d+$/');
        if ($product->category === 'laptops') {
            expect($product->ram_gb)->toBeGreaterThan(0)->and($product->storage_gb)->toBeGreaterThan(0)->and($product->processor)->not->toBeEmpty();
        } else {
            expect($product->ram_gb)->toBeNull()->and($product->storage_gb)->toBeNull();
        }
    }
    $this->seed(DatabaseSeeder::class);
    expect(Product::count())->toBe(292)->and(Product::where('category', 'laptops')->where('price', '<', 100000)->where('ram_gb', '>=', 32)->where('weight_kg', '<=', 1.5)->count())->toBeGreaterThan(0);
});
it('creates valid distinct factory products', function () {
    $products = Product::factory()->count(10)->create();
    expect($products->pluck('name')->unique())->toHaveCount(10);
    foreach ($products as $product) {
        expect($product->category)->toBeIn(Product::CATEGORIES)->and($product->price)->toBeGreaterThan(0);
    }
});
