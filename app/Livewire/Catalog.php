<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CatalogSearch;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class Catalog extends Component
{
    public string $query = '';

    public string $mode = 'traditional';

    public string $category = '';

    public string $brand = '';

    public string $minPrice = '';

    public string $maxPrice = '';

    public string $ram = '';

    public string $storage = '';

    public string $rating = '';

    public string $availability = '';

    public string $wireless = '';

    public string $sort = 'relevance';

    #[Locked]
    public string $submittedQuery = '';

    #[Locked]
    public int $page = 1;

    #[Locked]
    public array $products = [];

    #[Locked]
    public int $found = 0;

    #[Locked]
    public ?array $debug = null;

    #[Locked]
    public ?string $searchError = null;

    public function mount(): void
    {
        $this->runSearch();
    }

    public function search(): void
    {
        $this->validateInputs();
        $this->submittedQuery = $this->query;
        $this->page = 1;
        $this->runSearch();
    }

    public function updated(string $property): void
    {
        if ($property === 'query') {
            return;
        }
        if ($property === 'mode') {
            $this->submittedQuery = '';
        }
        if ($property === 'category') {
            if (! in_array($this->category, ['', 'laptops'])) {
                $this->ram = '';
                $this->storage = '';
            }
            if (! in_array($this->category, ['', 'keyboards', 'mice', 'headphones'])) {
                $this->wireless = '';
            }
        }
        $this->validateInputs();
        $this->page = 1;
        $this->runSearch();
    }

    public function resetFilters(): void
    {
        $this->reset(['category', 'brand', 'minPrice', 'maxPrice', 'ram', 'storage', 'rating', 'availability', 'wireless', 'sort']);
        $this->resetValidation();
        $this->page = 1;
        $this->runSearch();
    }

    public function nextPage(): void
    {
        if ($this->page * 12 < $this->found) {
            $this->page++;
            $this->runSearch();
        }
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
            $this->runSearch();
        }
    }

    protected function validateInputs(): void
    {
        $this->validate([
            'query' => ['string', 'max:1000'], 'mode' => [Rule::in(['traditional', 'natural'])],
            'category' => ['nullable', Rule::in(Product::CATEGORIES)], 'brand' => ['nullable', Rule::in(Product::distinct()->pluck('brand')->all())],
            'minPrice' => ['nullable', 'numeric', 'min:0', 'max:10000000'], 'maxPrice' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'ram' => ['nullable', Rule::in(['16', '24', '32', '64'])], 'storage' => ['nullable', Rule::in(['256', '512', '1024'])],
            'rating' => ['nullable', Rule::in(['3', '4', '4.5'])], 'availability' => ['nullable', Rule::in(['true', 'false'])], 'wireless' => ['nullable', Rule::in(['true', 'false'])],
            'sort' => [Rule::in(array_keys(CatalogSearch::SORTS))],
        ]);
        if ($this->minPrice !== '' && $this->maxPrice !== '' && (float) $this->minPrice > (float) $this->maxPrice) {
            $this->addError('maxPrice', 'Maximum price must be at least the minimum price.');
            throw ValidationException::withMessages(['maxPrice' => 'Maximum price must be at least the minimum price.']);
        }
        $this->resetValidation();
    }

    protected function runSearch(): void
    {
        $this->validateInputs();
        $this->products = [];
        $this->found = 0;
        $this->debug = null;
        $this->searchError = null;
        try {
            $result = app(CatalogSearch::class)->search($this->submittedQuery, $this->mode, $this->only(['category', 'brand', 'minPrice', 'maxPrice', 'ram', 'storage', 'rating', 'availability', 'wireless']), $this->sort, $this->page);
            $this->products = $result['products'];
            $this->found = $result['found'];
            $this->debug = $result['debug'];
        } catch (Throwable $exception) {
            // Exception text from upstream can include configuration: never render it.
            logger()->warning('DevStore search failed', ['exception_class' => $exception::class, 'mode' => $this->mode]);
            $this->searchError = $this->mode === 'natural' && ! config('devstore.nl_model_id')
                ? 'Natural Language Search needs a configured Typesense NL model. Set TYPESENSE_NL_MODEL_ID.'
                : 'Search is unavailable. Check the Typesense connection, product index and NL model configuration, then retry.';
        }
    }

    public function render()
    {
        return view('livewire.catalog', ['brands' => Product::distinct()->orderBy('brand')->pluck('brand'), 'categories' => Product::CATEGORIES, 'sorts' => CatalogSearch::SORTS])->layout('components.layouts.app');
    }
}
