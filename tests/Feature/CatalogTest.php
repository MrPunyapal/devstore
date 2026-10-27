<?php

use App\Livewire\Catalog;
use App\Models\Product;
use App\Services\CatalogSearch;
use Livewire\Livewire;

function fakeCatalogSearch(): void
{
    test()->mock(CatalogSearch::class, function ($mock) {
        $mock->shouldReceive('search')->andReturn(['products' => [], 'found' => 30, 'debug' => null]);
    });
}
it('renders the single public page', function () {
    fakeCatalogSearch();
    $this->get('/')->assertOk()->assertSee('DevStore')->assertSee('Traditional Search')->assertSee('Natural Language Search');
});
it('submits and preserves the NL query without searching on typing', function () {
    $mock = $this->mock(CatalogSearch::class);
    $mock->shouldReceive('search')->once()->with('', 'traditional', Mockery::type('array'), 'relevance', 1)->andReturn(['products' => [], 'found' => 0, 'debug' => null]);
    $mock->shouldReceive('search')->once()->with('', 'natural', Mockery::type('array'), 'relevance', 1)->andReturn(['products' => [], 'found' => 0, 'debug' => null]);
    $query = 'A laptop under ₹1 lakh with 32GB RAM';
    $mock->shouldReceive('search')->once()->with($query, 'natural', Mockery::type('array'), 'relevance', 1)->andReturn(['products' => [], 'found' => 0, 'debug' => null]);
    Livewire::test(Catalog::class)->set('mode', 'natural')->set('query', $query)->assertSet('submittedQuery', '')->call('search')->assertSet('submittedQuery', $query)->assertSet('query', $query);
});
it('paginates and resets the page when filters change', function () {
    fakeCatalogSearch();
    Livewire::test(Catalog::class)->call('nextPage')->assertSet('page', 2)->call('nextPage')->assertSet('page', 3)->call('nextPage')->assertSet('page', 3)->set('category', 'laptops')->assertSet('page', 1)->call('previousPage')->assertSet('page', 1);
});
it('clears irrelevant filters when switching category', function () {
    fakeCatalogSearch();
    Livewire::test(Catalog::class)->set('ram', '32')->set('storage', '512')->set('category', 'monitors')->assertSet('ram', '')->assertSet('storage', '')->set('category', 'mice')->set('wireless', 'true')->set('category', 'laptops')->assertSet('wireless', '');
});
it('resets constraints but preserves a submitted query', function () {
    fakeCatalogSearch();
    Livewire::test(Catalog::class)->set('query', 'Dell')->call('search')->set('category', 'monitors')->set('sort', 'rating')->call('resetFilters')->assertSet('category', '')->assertSet('sort', 'relevance')->assertSet('submittedQuery', 'Dell');
});
it('rejects invalid filters and inverted prices', function () {
    fakeCatalogSearch();
    Livewire::test(Catalog::class)->set('minPrice', '5000')->set('maxPrice', '4000')->assertHasErrors('maxPrice');
    Livewire::test(Catalog::class)->set('category', 'invalid')->assertHasErrors('category');
    Livewire::test(Catalog::class)->set('sort', 'injected')->assertHasErrors('sort');
});
it('does not expose sensitive upstream exception messages', function () {
    $this->mock(CatalogSearch::class, fn ($mock) => $mock->shouldReceive('search')->andThrow(new RuntimeException('api_key=secret-test-key')));
    Livewire::test(Catalog::class)->assertSee('Search needs attention')->assertDontSee('secret-test-key')->assertSet('products', []);
});
it('escapes product text in results', function () {
    $product = Product::factory()->make(['id' => 1, 'name' => '<script>alert(1)</script>'])->toSearchableArray();
    $this->mock(CatalogSearch::class, fn ($mock) => $mock->shouldReceive('search')->andReturn(['products' => [$product], 'found' => 1, 'debug' => null]));
    Livewire::test(Catalog::class)->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
});
