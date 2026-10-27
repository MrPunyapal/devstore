<?php

use App\Services\CatalogSearch;

it('builds traditional keyword search with explicit constraints', function () {
    $params = app(CatalogSearch::class)->parameters('ThinkPad', 'traditional', ['category' => 'laptops', 'brand' => 'Lenovo', 'minPrice' => '50000', 'maxPrice' => '100000', 'ram' => '32', 'storage' => '512', 'rating' => '4', 'availability' => 'true', 'wireless' => 'false'], 'price_asc');
    expect($params['q'])->toBe('ThinkPad')->and($params['filter_by'])->toBe('category:=`laptops` && brand:=`Lenovo` && price:>=50000 && price:<=100000 && ram_gb:>=32 && storage_gb:>=512 && rating:>=4 && in_stock:=true && wireless:=false')->and($params['sort_by'])->toBe('price:asc')->and($params)->not->toHaveKeys(['nl_query', 'nl_model_id']);
});
it('passes the original natural-language query to Typesense', function () {
    config(['devstore.nl_model_id' => 'existing-model']);
    $query = 'I need a laptop under ₹1 lakh with 32GB RAM';
    $params = app(CatalogSearch::class)->parameters($query, 'natural', ['availability' => 'true'], 'relevance');
    expect($params)->toHaveKey('q', $query)->toHaveKey('nl_query', true)->toHaveKey('nl_model_id', 'existing-model')->toHaveKey('filter_by', 'in_stock:=true')->not->toHaveKey('sort_by');
});
it('uses wildcard browsing without an NL model call', function () {
    expect(app(CatalogSearch::class)->parameters('  ', 'natural', [], 'relevance'))->toHaveKey('q', '*')->not->toHaveKey('nl_query');
});
it('requires an actual model for a natural-language query', function () {
    config(['devstore.nl_model_id' => '']);
    app(CatalogSearch::class)->parameters('Find a portable laptop', 'natural', [], 'relevance');
})->throws(RuntimeException::class);
it('maps deterministic sorting options', function ($sort, $field) {
    expect(app(CatalogSearch::class)->parameters('', 'traditional', [], $sort))->toHaveKey('sort_by', $field);
})->with(['price ascending' => ['price_asc', 'price:asc'], 'price descending' => ['price_desc', 'price:desc'], 'rating' => ['rating', 'rating:desc'], 'newest' => ['newest', 'created_at:desc']]);
it('rejects Typesense delimiter injection', function () {
    app(CatalogSearch::class)->parameters('', 'traditional', ['brand' => 'Dell` || price:>0'], 'relevance');
})->throws(RuntimeException::class);
