<div class="mx-auto max-w-7xl px-4 py-5 sm:px-8 sm:py-8">
    <header class="mb-6 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3"><span class="grid size-11 place-items-center rounded-xl bg-slate-900 font-mono text-lg text-white" aria-hidden="true">&gt;_</span><div><h1 class="text-2xl font-bold tracking-tight">DevStore</h1><p class="text-sm text-slate-500">Developer hardware</p></div></div>
        <span class="text-xs text-slate-400">Powered by Typesense</span>
    </header>
    <div class="grid items-start gap-6 lg:grid-cols-[220px_1fr]">
        <details open class="rounded-xl border border-stone-200 bg-white p-4"><summary class="text-sm font-medium text-slate-600">Filters</summary><aside class="pt-4">
            <div class="mb-4 flex justify-end"><button wire:click="resetFilters" class="text-xs text-teal-700 hover:underline">Reset</button></div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                <label class="filter-label">Category<select wire:model.live="category"><option value="">All categories</option>@foreach($categories as $item)<option value="{{ $item }}">{{ ucfirst($item) }}</option>@endforeach</select></label>
                <label class="filter-label">Brand<select wire:model.live="brand"><option value="">All brands</option>@foreach($brands as $item)<option value="{{ $item }}">{{ $item }}</option>@endforeach</select></label>
                <div><span class="filter-label">Price range · ₹</span><div class="mt-1.5 grid grid-cols-2 gap-2"><label><span class="sr-only">Minimum price</span><input wire:model.blur="minPrice" type="number" min="0" max="10000000" placeholder="Min" class="filter-input"></label><label><span class="sr-only">Maximum price</span><input wire:model.blur="maxPrice" type="number" min="0" max="10000000" placeholder="Max" class="filter-input"></label></div></div>
                @if(in_array($category, ['', 'laptops']))
                    <label class="filter-label">Minimum RAM<select wire:model.live="ram"><option value="">Any RAM</option>@foreach([16,24,32,64] as $value)<option value="{{ $value }}">{{ $value }} GB+</option>@endforeach</select></label>
                    <label class="filter-label">Minimum storage<select wire:model.live="storage"><option value="">Any storage</option>@foreach([256,512,1024] as $value)<option value="{{ $value }}">{{ $value }} GB+</option>@endforeach</select></label>
                @endif
                <label class="filter-label">Minimum rating<select wire:model.live="rating"><option value="">Any rating</option>@foreach([3,4,4.5] as $value)<option value="{{ $value }}">{{ $value }} / 5+</option>@endforeach</select></label>
                <label class="filter-label">Availability<select wire:model.live="availability"><option value="">All products</option><option value="true">In stock</option><option value="false">Out of stock</option></select></label>
                @if(in_array($category, ['', 'keyboards', 'mice', 'headphones']))<label class="filter-label">Connectivity<select wire:model.live="wireless"><option value="">Any connectivity</option><option value="true">Wireless</option><option value="false">Wired</option></select></label>@endif
            </div>
        </aside></details>
        <main class="min-w-0" aria-labelledby="results-heading">
    <section class="mb-7 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="search-heading">
        <h2 id="search-heading" class="sr-only">Search hardware</h2>
        <form wire:submit="search" class="flex flex-col gap-3 xl:flex-row xl:items-center">
            <fieldset class="flex shrink-0 flex-wrap gap-1 self-start rounded-xl bg-stone-100 p-1"><legend class="sr-only">Search mode</legend>
                @foreach(['traditional' => 'Traditional Search', 'natural' => 'Natural Language Search'] as $value => $label)
                    <label class="cursor-pointer whitespace-nowrap rounded-lg px-3 py-2 text-sm {{ $mode === $value ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600' }}"><input class="sr-only peer" type="radio" wire:model.live="mode" value="{{ $value }}"><span class="peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-4">{{ $label }}</span></label>
                @endforeach
            </fieldset>
            <div class="flex min-w-0 flex-1 gap-3">
                <div class="min-w-0 flex-1"><label class="sr-only" for="query">{{ $mode === 'natural' ? 'Describe what you need' : 'Search products' }}</label><input id="query" type="search" wire:model="query" maxlength="1000" class="h-11 w-full rounded-xl border border-stone-300 bg-stone-50 px-4 text-sm" placeholder="{{ $mode === 'natural' ? 'Describe what you need…' : 'Search products…' }}"></div>
                <button class="h-11 shrink-0 rounded-xl bg-teal-700 px-5 text-sm font-semibold text-white hover:bg-teal-800 disabled:opacity-50" wire:loading.attr="disabled">Search <span aria-hidden="true">→</span></button>
            </div>
        </form>
        @if($errors->any())<ul role="alert" class="mt-3 text-sm text-red-700">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    </section>

            <div class="mb-5 flex flex-wrap items-center justify-between gap-3"><div><h2 id="results-heading" class="text-lg font-semibold">{{ number_format($found) }} {{ $found === 1 ? 'result' : 'results' }}</h2></div><label class="flex items-center gap-2 text-sm text-slate-600">Sort<select wire:model.live="sort" class="rounded-lg border border-stone-300 bg-white px-3 py-2">@foreach($sorts as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label></div>
            <div wire:loading role="status" class="mb-4 rounded-xl bg-teal-50 p-4 text-sm text-teal-800">Searching…</div>
            <div wire:loading.class="opacity-40" class="transition-opacity">
                @if($searchError)<div role="alert" class="rounded-xl border border-amber-200 bg-amber-50 p-6"><h3 class="font-semibold text-amber-900">Search needs attention</h3><p class="mt-2 text-sm text-amber-900">{{ $searchError }}</p><button wire:click="search" class="mt-4 text-sm font-semibold text-amber-900 underline">Retry search</button></div>
                @elseif(count($products) === 0)<div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center"><h3 class="text-lg font-semibold">No matching hardware</h3><p class="mt-2 text-sm text-slate-500">Try fewer constraints, a different query, or reset the filters.</p></div>
                @else<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($products as $product)
                        <article wire:key="product-{{ $product['id'] }}" class="flex flex-col rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-center justify-between gap-2"><span class="rounded-md bg-stone-100 px-2 py-1 font-mono text-[10px] uppercase tracking-wide text-slate-600">{{ $product['category'] }}</span><span class="text-xs font-medium {{ $product['in_stock'] ? 'text-teal-700' : 'text-slate-400' }}">{{ $product['in_stock'] ? '● In stock' : 'Out of stock' }}</span></div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-teal-700">{{ $product['brand'] }}</p><h3 class="mt-1 text-base font-semibold leading-6">{{ $product['name'] }}</h3>
                            <p class="mt-2 line-clamp-2 text-sm leading-5 text-slate-500">{{ $product['description'] }}</p>
                            <div class="mt-4 flex flex-wrap gap-1.5 text-xs text-slate-600">
                                @if(isset($product['ram_gb']))<span class="spec">{{ $product['ram_gb'] }} GB RAM</span>@endif
                                @if(isset($product['storage_gb']))<span class="spec">{{ $product['storage_gb'] }} GB SSD</span>@endif
                                @if(isset($product['screen_size']))<span class="spec">{{ $product['screen_size'] }}″</span>@endif
                                <span class="spec">{{ $product['weight_kg'] }} kg</span>
                                @if(isset($product['wireless']))<span class="spec">{{ $product['wireless'] ? 'Wireless' : 'Wired' }}</span>@endif
                            </div>
                            <div class="mt-auto flex items-end justify-between gap-2 pt-6"><p class="text-xl font-semibold tracking-tight">₹{{ number_format($product['price'], 0, '.', ',') }}</p><p class="text-sm text-slate-500"><span class="text-amber-600" aria-hidden="true">★</span> {{ number_format($product['rating'], 1) }} <span class="text-xs">/ 5</span></p></div>
                        </article>
                    @endforeach
                </div>@endif
            </div>
            @if($found > 12)<nav aria-label="Results pagination" class="mt-6 flex items-center justify-between"><button wire:click="previousPage" @disabled($page <= 1) wire:loading.attr="disabled" class="rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm disabled:opacity-40">← Previous</button><span class="text-sm text-slate-500">Page {{ $page }} of {{ (int) ceil($found / 12) }}</span><button wire:click="nextPage" @disabled($page * 12 >= $found) wire:loading.attr="disabled" class="rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm disabled:opacity-40">Next →</button></nav>@endif
            @if($debug && app()->environment('local') && config('devstore.search_debug'))<details class="mt-6 text-slate-500"><summary class="cursor-pointer font-mono text-xs">Search details</summary><pre class="mt-4 overflow-x-auto whitespace-pre-wrap break-all font-mono text-xs leading-6">{{ json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details>@endif
        </main>
    </div>
    <footer class="mt-10 border-t border-stone-200 pt-5 text-xs leading-5 text-slate-400">Illustrative catalog · Prices in INR</footer>
</div>
