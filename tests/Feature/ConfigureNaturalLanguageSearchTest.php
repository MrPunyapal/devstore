<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'scout.typesense.client-settings.api_key' => 'fixture-typesense-key',
        'scout.typesense.client-settings.nodes' => [['host' => '127.0.0.1', 'port' => '8108', 'protocol' => 'http', 'path' => '']],
        'devstore.nl_model_id' => 'demo-model',
    ]);
    Http::preventStrayRequests();
});

it('registers the hidden provider key with Typesense without printing the response', function () {
    Http::fake([
        'http://127.0.0.1:8108/nl_search_models/demo-model' => Http::response([], 404),
        'http://127.0.0.1:8108/nl_search_models' => Http::response(['api_key' => 'fixture-gemini-key'], 201),
    ]);

    $this->artisan('devstore:configure-nl')
        ->expectsQuestion('Gemini API key', 'fixture-gemini-key')
        ->expectsOutputToContain('Registered Typesense NL model')
        ->doesntExpectOutputToContain('fixture-gemini-key')
        ->assertSuccessful();

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && $request->url() === 'http://127.0.0.1:8108/nl_search_models'
        && $request->hasHeader('X-TYPESENSE-API-KEY', 'fixture-typesense-key')
        && $request['api_key'] === 'fixture-gemini-key'
        && $request['id'] === 'demo-model'
        && $request['model_name'] === 'google/gemini-3.1-flash-lite');
    Http::assertSentCount(2);
});

it('preserves an existing server model without asking for a provider key', function () {
    Http::fake(['*' => Http::response(['id' => 'demo-model', 'api_key' => 'fixture-private-key'])]);

    $this->artisan('devstore:configure-nl')
        ->expectsOutputToContain('already exists')
        ->doesntExpectOutputToContain('fixture-private-key')
        ->assertSuccessful();
    Http::assertSentCount(1);
});

it('does not trust or print a failed model lookup', function () {
    Http::fake(['*' => Http::response(['message' => 'fixture-private-key'], 401)]);

    $this->artisan('devstore:configure-nl')
        ->expectsOutputToContain('HTTP 401')
        ->doesntExpectOutputToContain('fixture-private-key')
        ->assertFailed();
    Http::assertSentCount(1);
});

it('does not register an empty provider key', function () {
    Http::fake(['*' => Http::response([], 404)]);

    $this->artisan('devstore:configure-nl')
        ->expectsQuestion('Gemini API key', '')
        ->expectsOutputToContain('Nothing was registered')
        ->assertFailed();
    Http::assertSentCount(1);
});

it('keeps connection exception details out of console output', function () {
    Http::fake(fn () => throw new ConnectionException('fixture-private-key'));

    $this->artisan('devstore:configure-nl')
        ->expectsOutputToContain('Could not reach Typesense')
        ->doesntExpectOutputToContain('fixture-private-key')
        ->assertFailed();
});
