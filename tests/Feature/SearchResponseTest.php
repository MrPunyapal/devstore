<?php

use App\Services\CatalogSearch;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\TypesenseEngine;

// These are transport/error fixtures, not assertions about an LLM's accuracy.
function typesenseResponseFixture(array $response): void
{
    config(['scout.driver' => 'typesense', 'devstore.nl_model_id' => 'configured-model']);
    $engine = Mockery::mock(TypesenseEngine::class);
    $engine->shouldReceive('paginate')->once()->with(Mockery::on(fn ($builder) => $builder->options['nl_query'] === true && $builder->options['nl_model_id'] === 'configured-model' && $builder->options['timeout_ms'] === 60000), 12, 1)->andReturn($response);
    if (! isset($response['error']) && ! isset($response['parsed_nl_query']['error']) && ! empty($response['parsed_nl_query']['generated_params'])) {
        $engine->shouldReceive('paginate')->once()->with(Mockery::on(fn ($builder) => ! isset($builder->options['nl_query']) && ! isset($builder->options['nl_model_id'])), 12, 2)->andReturn(['hits' => [], 'found' => 0, 'search_time_ms' => 3]);
    }
    app(EngineManager::class)->extend('typesense', fn () => $engine);
}

it('rejects an upstream NL fallback response', function ($response) {
    typesenseResponseFixture($response);
    app(CatalogSearch::class)->search('a portable laptop', 'natural', [], 'relevance', 2);
})->with([
    'top-level error' => [['error' => 'provider authentication failed', 'hits' => [], 'found' => 0]],
    'missing interpretation' => [['hits' => [], 'found' => 0]],
    'nested error' => [['parsed_nl_query' => ['error' => 'timeout'], 'hits' => [], 'found' => 0]],
    'empty interpretation after timeout' => [['parsed_nl_query' => ['generated_params' => []], 'hits' => [], 'found' => 0]],
])->throws(RuntimeException::class);

it('allowlists inspector metadata and hides it outside local development', function ($environment, $debugEnabled, $visible) {
    app()->instance('env', $environment);
    config(['devstore.search_debug' => $debugEnabled]);
    typesenseResponseFixture(['hits' => [], 'found' => 0, 'search_time_ms' => 3, 'parsed_nl_query' => ['parse_time_ms' => 12, 'generated_params' => ['q' => '*', 'api_key' => 'fixture-secret'], 'augmented_params' => ['filter_by' => 'category:=laptops', 'nl_model_id' => 'private-id'], 'llm_response' => ['content' => 'private-response']]]);
    $result = app(CatalogSearch::class)->search('a portable laptop', 'natural', [], 'relevance', 2);
    if ($visible) {
        expect($result['debug']['sent']['page'])->toBe(1)->and($result['debug']['applied']['page'])->toBe(2)->and($result['debug']['request_count'])->toBe(2)->and($result['debug']['parse_time_ms'])->toBe(12)->and(json_encode($result['debug']))->not->toContain('fixture-secret', 'private-id', 'private-response');
    } else {
        expect($result['debug'])->toBeNull();
    }
})->with(['local enabled' => ['local', true, true], 'local disabled' => ['local', false, false], 'production enabled' => ['production', true, false]]);

it('replays generated parameters while retaining explicit constraints and sorting', function () {
    config(['scout.driver' => 'typesense', 'devstore.nl_model_id' => 'configured-model']);
    $engine = Mockery::mock(TypesenseEngine::class);
    $engine->shouldReceive('paginate')->once()->with(Mockery::on(fn ($builder) => $builder->query === 'a good laptop' && $builder->options['nl_query'] === true), 12, 1)->andReturn(['hits' => [], 'found' => 0, 'parsed_nl_query' => ['generated_params' => ['q' => '', 'filter_by' => 'category:laptops || category:monitors', 'sort_by' => 'rating:desc']]]);
    $engine->shouldReceive('paginate')->once()->with(Mockery::on(fn ($builder) => $builder->query === '*' && $builder->options['filter_by'] === '(in_stock:=true) && (category:laptops || category:monitors)' && $builder->options['sort_by'] === 'price:asc' && ! isset($builder->options['nl_query'])), 12, 2)->andReturn(['hits' => [], 'found' => 0]);
    app(EngineManager::class)->extend('typesense', fn () => $engine);
    expect(app(CatalogSearch::class)->search('a good laptop', 'natural', ['availability' => 'true'], 'price_asc', 2)['found'])->toBe(0);
});
