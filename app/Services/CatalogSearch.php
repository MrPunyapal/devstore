<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Arr;
use RuntimeException;

class CatalogSearch
{
    public const SORTS = ['relevance' => 'Relevance', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'rating' => 'Highest rating', 'newest' => 'Newest'];

    public function parameters(string $query, string $mode, array $filters, string $sort): array
    {
        $clauses = [];
        foreach (['category', 'brand'] as $field) {
            if (($filters[$field] ?? '') !== '') {
                // Typesense backtick literals; reject control / delimiter characters.
                $value = $filters[$field];
                if (preg_match('/[`\\\\\x00-\x1f]/', $value)) {
                    throw new RuntimeException('Invalid filter value.');
                }
                $clauses[] = $field.':=`'.$value.'`';
            }
        }
        foreach (['minPrice' => ['price', '>='], 'maxPrice' => ['price', '<='], 'ram' => ['ram_gb', '>='], 'storage' => ['storage_gb', '>='], 'rating' => ['rating', '>=']] as $key => [$field, $operator]) {
            if (($filters[$key] ?? '') !== '') {
                if (! is_numeric($filters[$key]) || $filters[$key] < 0) {
                    throw new RuntimeException('Invalid numeric filter.');
                }
                $clauses[] = $field.':'.$operator.(float) $filters[$key];
            }
        }
        foreach (['availability' => 'in_stock', 'wireless' => 'wireless'] as $key => $field) {
            if (($filters[$key] ?? '') !== '') {
                if (! in_array($filters[$key], ['true', 'false'], true)) {
                    throw new RuntimeException('Invalid boolean filter.');
                }
                $clauses[] = $field.':='.$filters[$key];
            }
        }
        $params = ['q' => trim($query) === '' ? '*' : $query, 'query_by' => config('devstore.query_by'), 'filter_by' => implode(' && ', $clauses), 'per_page' => 12];
        if (! array_key_exists($sort, self::SORTS)) {
            throw new RuntimeException('Invalid sort.');
        }
        $sortBy = match ($sort) {
            'price_asc' => 'price:asc', 'price_desc' => 'price:desc', 'rating' => 'rating:desc', 'newest' => 'created_at:desc', default => null
        };
        if ($sortBy !== null) {
            $params['sort_by'] = $sortBy;
        }
        if ($mode === 'natural' && trim($query) !== '') {
            if (! config('devstore.nl_model_id')) {
                throw new RuntimeException('Natural Language Search needs a configured Typesense NL model. Set TYPESENSE_NL_MODEL_ID.');
            }
            $params['nl_query'] = true;
            $params['nl_model_id'] = config('devstore.nl_model_id');
            $params['timeout_ms'] = 60000;
        }

        return $params;
    }

    public function search(string $query, string $mode, array $filters, string $sort, int $page): array
    {
        if (config('scout.driver') !== 'typesense') {
            throw new RuntimeException('DevStore requires SCOUT_DRIVER=typesense.');
        }
        $params = $this->parameters($query, $mode, $filters, $sort);
        $applied = $params;
        $parsed = [];
        $requestCount = 1;
        if (isset($params['nl_query'])) {
            // Typesense 30.2 can execute the original sentence despite reporting a rewritten q.
            // Let Typesense interpret it, then execute only the returned search parameters.
            $interpretation = $this->execute($params, 1);
            $parsed = $interpretation['parsed_nl_query'] ?? [];
            $generated = Arr::only($parsed['generated_params'] ?? [], ['q', 'filter_by', 'sort_by']);
            if (isset($interpretation['error']) || isset($parsed['error']) || $generated === []) {
                throw new RuntimeException('Typesense did not return a successful natural-language interpretation.');
            }
            foreach ($generated as $value) {
                if (! is_string($value)) {
                    throw new RuntimeException('Typesense returned an invalid natural-language search parameter.');
                }
            }
            $applied = Arr::except($params, ['nl_query', 'nl_model_id']);
            $applied['q'] = trim($generated['q'] ?? '') === '' ? '*' : $generated['q'];
            $explicitFilter = $params['filter_by'];
            $generatedFilter = $generated['filter_by'] ?? '';
            $applied['filter_by'] = $explicitFilter !== '' && $generatedFilter !== ''
                ? '('.$explicitFilter.') && ('.$generatedFilter.')'
                : ($explicitFilter ?: $generatedFilter);
            if (! isset($params['sort_by']) && ($generated['sort_by'] ?? '') !== '') {
                $applied['sort_by'] = $generated['sort_by'];
            }
            $requestCount = 2;
        }
        $raw = $this->execute($applied, $page);
        if (isset($raw['error'])) {
            throw new RuntimeException('Typesense could not execute the search.');
        }
        $debug = null;
        if (app()->environment('local') && config('devstore.search_debug')) {
            $allowed = ['q', 'query_by', 'filter_by', 'sort_by', 'page', 'per_page', 'timeout_ms'];
            $debug = ['request_count' => $requestCount, 'sent' => Arr::only($params, $allowed) + ['page' => $requestCount === 2 ? 1 : $page, 'nl_query' => isset($params['nl_query'])], 'generated' => Arr::only($parsed['generated_params'] ?? [], $allowed), 'applied' => Arr::only($applied, $allowed) + ['page' => $page], 'parse_time_ms' => $parsed['parse_time_ms'] ?? null, 'search_time_ms' => $raw['search_time_ms'] ?? null];
        }

        return ['products' => array_map(fn ($hit) => Arr::only($hit['document'], ['id', 'name', 'brand', 'category', 'description', 'price', 'ram_gb', 'storage_gb', 'processor', 'screen_size', 'weight_kg', 'rating', 'in_stock', 'wireless', 'created_at']), $raw['hits'] ?? []), 'found' => (int) ($raw['found'] ?? 0), 'debug' => $debug];
    }

    protected function execute(array $params, int $page): array
    {
        $builder = Product::search($params['q'])->options($params);

        return $builder->model->searchableUsing()->paginate($builder, 12, $page);
    }
}
