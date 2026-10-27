# DevStore

A small Laravel demo for comparing keyword search with **Typesense's built-in Natural Language Search** on a developer hardware catalog.

One page, 292 illustrative products, seven categories, explicit filters and sorting. Built with Laravel 13, Livewire 4, Tailwind CSS 4, Laravel Scout, Typesense and SQLite. Tests use Pest; formatting uses Pint. No starter kit, authentication, checkout or admin panel.

This is an experiment. Product configurations, accessory kits, INR prices, ratings and availability are synthetic. They are not verified retail offers or purchasing advice. Real observations and limitations are recorded in [docs/experiment.md](docs/experiment.md).

## Requirements

- **PHP 8.4+**, Composer 2, and the usual Laravel extensions, including PDO SQLite, SQLite3, mbstring, curl, openssl, fileinfo, XML and DOM. PHP 8.4 is the baseline for this project's locked dependencies and Pest 4.
- **Node.js 22.12+** and npm for the Tailwind/Vite build.
- **Typesense 30.2**, the server version used for this experiment. Use Docker Compose below or an existing server. Docker is needed only for that optional server setup, not for Laravel itself.
- **For NL Search:** a model registered in Typesense using one of its [supported providers or self-hosted backends](https://typesense.org/docs/30.2/api/natural-language-search.html#supported-model-types). Gemini is the example used below, not a requirement. Traditional Search works without an AI key.

The application runs on Windows, macOS and Linux. The commands below are run from the repository root after cloning or downloading it.

## 1. Install Laravel dependencies and seed SQLite

```sh
git clone https://github.com/MrPunyapal/devstore.git
cd devstore
```

```sh
composer install
```

Copy the example environment file **once on a fresh checkout**:

```sh
# macOS / Linux / WSL
cp .env.example .env
```

```powershell
# Windows PowerShell
Copy-Item .env.example .env
```

Then, on any platform:

```sh
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
npm ci
npm run build
```

`DB_CONNECTION=sqlite` is already in `.env.example`. Leave `DB_DATABASE` unset to use `database/database.sqlite`; an optional custom value should be an absolute path. The seeder can be rerun without creating duplicate product names. It skips search syncing so database setup works before Typesense is available.

For an initial local checkout, `composer setup` performs the same dependency, environment, key, SQLite, seed and asset steps. It does not start Typesense, register an AI model or index products. Use the explicit steps when updating an existing installation; do not overwrite its `.env` or regenerate its application key.

## 2. Configure and start Typesense

There are **two separate API keys**:

| Key | Used by | Where it belongs |
| --- | --- | --- |
| Typesense API key | Laravel / Scout, to access your search server | `TYPESENSE_API_KEY` in Laravel's ignored `.env` |
| AI provider credentials, when required | Typesense, to interpret natural-language queries | The NL model registered inside Typesense; step 4 shows Gemini and other provider options |

Laravel sends searches to Typesense. AI provider credentials belong in Typesense's model configuration, not Laravel's `.env`.

### Local server with Docker Compose

Generate a new local Typesense key:

```sh
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

Copy that generated value into `TYPESENSE_API_KEY` in `.env`. The relevant settings are:

```dotenv
SCOUT_DRIVER=typesense
TYPESENSE_HOST=127.0.0.1
TYPESENSE_PORT=8108
TYPESENSE_PROTOCOL=http
TYPESENSE_PATH=
TYPESENSE_API_KEY=
TYPESENSE_NL_MODEL_ID=gemini-model
TYPESENSE_QUERY_BY=name,description,brand,category,processor
TYPESENSE_CONNECTION_TIMEOUT_SECONDS=60
```

`TYPESENSE_API_KEY` is deliberately blank above: fill it with your generated key before continuing. Docker Compose reads the same `.env` and supplies this key to the server.

```sh
docker compose up -d typesense
```

The included `compose.yaml` pins Typesense 30.2, binds port 8108 to localhost and persists its database and model configuration in a Docker volume. It starts only Typesense; Laravel runs on the host. Check health after startup:

```sh
# macOS / Linux / WSL
curl --fail http://127.0.0.1:8108/health
```

```powershell
# Windows PowerShell
Invoke-RestMethod http://127.0.0.1:8108/health
```

Expect `{"ok":true}`. Use `docker compose logs typesense` to diagnose startup issues. `docker compose stop` stops the service without removing its data. Do not use `down -v` unless you intend to discard this demo server's persisted data.

### Existing server, Typesense Cloud or native installation

Skip Docker and set the connection variables for your server. `TYPESENSE_HOST` is a hostname without a scheme; hosted servers usually use `TYPESENSE_PROTOCOL=https` and `TYPESENSE_PORT=443`. `TYPESENSE_PATH` is optional for a reverse proxy.

Use a dedicated experiment instance or ensure the `products` collection is available for this demo. The setup key needs collection creation/import/search and NL-model management permissions. See the official [Typesense installation guide](https://typesense.org/docs/guide/install-typesense.html) for native Linux, macOS and WSL options. On WSL, localhost forwarding may work; otherwise use the server's reachable WSL address. Keep any machine-specific server workarounds outside this repository.

After editing `.env`:

```sh
php artisan config:clear
```

## 3. Index the catalog

With Typesense healthy and `.env` configured:

```sh
php artisan scout:import 'App\Models\Product'
```

Scout creates the `products` collection and imports all 292 seeded records. Its schema is in `config/scout.php`. Prices and measurements are numeric, IDs are strings, booleans retain their types, and dates use Unix timestamps. Inapplicable attributes such as mouse RAM are omitted. Reimport after rerunning the seeder; ordinary Eloquent model writes sync through Scout synchronously.

Importing does not migrate an existing collection schema. If you change it, rebuild only your own disposable `products` collection and reimport; do not delete a shared collection.

**Traditional Search is ready at this point.** Continue to step 4 only if you want Natural Language Search.

## 4. Configure a Natural Language Search model in Typesense

**Gemini is optional.** I used Gemini for this experiment because it offered a free tier. You can use another provider or self-hosted model supported by your Typesense version. See [Typesense's supported model types and registration examples](https://typesense.org/docs/30.2/api/natural-language-search.html#supported-model-types).

Google's free tier applies to eligible models and has limits; it is not a promise that every Gemini model or all usage is free. Check the current [Gemini API pricing and free-tier details](https://ai.google.dev/gemini-api/docs/pricing) for your account and selected model.

### Option A: Gemini, as used in this demo

Obtain a Gemini API key from [Google AI Studio](https://aistudio.google.com/apikey). Confirm the account can access the selected model; provider quotas, rate limits and usage charges apply. NL queries and catalog schema information are processed by that provider through Typesense.

With `TYPESENSE_NL_MODEL_ID=gemini-model` in `.env`, run:

```sh
php artisan devstore:configure-nl
```

Paste the **Gemini key at the hidden prompt**. It is sent to your configured Typesense server and is not written to Laravel's `.env`, a JSON file or this repository. The command prints no model-response body. If that model ID already exists, the command leaves its configuration unchanged and does not ask for a key.

The default model is `google/gemini-3.1-flash-lite`, used in this experiment. You can select another available Gemini model supported by your Typesense setup:

```sh
php artisan devstore:configure-nl --model=google/your-supported-gemini-model
```

Model names and availability can change; check [Google's model documentation](https://ai.google.dev/gemini-api/docs/models/gemini-3.1-flash-lite) and the [Typesense NL API](https://typesense.org/docs/30.2/api/natural-language-search.html). The prefix `google/` is Typesense's provider identifier. No Google SDK is installed in Laravel.

### Option B: Another Typesense-supported provider

1. Register the provider/model and its required credentials or endpoint **inside Typesense**, following the [NL model registration documentation](https://typesense.org/docs/30.2/api/natural-language-search.html#create-a-natural-language-search-model).
2. Set the registered model's ID in Laravel's `.env`:

   ```dotenv
   TYPESENSE_NL_MODEL_ID=your-nl-model-id
   ```

3. Run `php artisan config:clear`, select Natural Language Search, and submit a query.

The search code passes this ID to Typesense, so switching providers requires no Laravel search-code changes or additional provider SDK. `gemini-model` is just the example ID used for this experiment. The `devstore:configure-nl` helper is specifically for Gemini; skip it for other providers or an already configured model. Manage credential changes inside Typesense rather than committing provider configuration here.

## 5. Run the app

```sh
php artisan serve --host=127.0.0.1 --port=8000
```

Open **[http://127.0.0.1:8000](http://127.0.0.1:8000)**. The example `APP_URL` uses this address. No queue worker is needed. The production CSS built earlier is enough; for CSS editing, run `npm run dev` in another terminal.

With Laravel Herd, run `herd link devstore` from the project directory and use [http://devstore.test](http://devstore.test), setting `APP_URL=http://devstore.test` locally. No `artisan serve` process is needed with Herd.

## Try both modes

Reset filters before a comparison, keep the query and viewport the same, and submit again after switching modes. Typing alone does not call the model. Filters and pagination changes rerun the submitted request and can invoke the AI provider again.

**Traditional Search:** type `ThinkPad` and submit. Use the sidebar for laptops, maximum price `100000` and minimum RAM `32`. This sends keywords and explicit constraints to Typesense. Blank input browses the catalog. Reset clears constraints and ordering while keeping the query.

**Natural Language Search:** select that mode and submit:

> Find ThinkPad laptops with at least 32GB RAM under ₹100000

Other queries to explore:

- I need a lightweight laptop for Laravel development under ₹1 lakh with at least 32GB RAM
- Show me a large monitor for coding under ₹50000
- I need a wireless keyboard and mouse setup under ₹15000
- Find me something portable with a good rating and at least 16GB RAM

Selected sidebar constraints still apply. “Under” may produce a strict `<` filter, while the maximum-price control uses `<=`. The model may infer additional constraints such as stock availability. A sentence in Traditional mode can return no hits; it does not interpret the sentence as numeric filters. Counts and interpretations are observations, not guarantees. The keyboard-and-mouse query returns individual items, not a basket optimized for a combined budget.

## Typesense 30.2 compatibility and inspection

This demo includes a two-request workaround for a [reported Typesense 30.x regression](https://github.com/typesense/typesense/issues/2910): the server can report a rewritten query while executing the original sentence.

1. Send the original sentence to Typesense with `nl_query=true`, the configured model ID and `timeout_ms=60000`.
2. Require non-empty generated parameters from a successful interpretation, then execute Typesense's returned `q`, `filter_by` and `sort_by` without `nl_query`. Empty generated query text becomes `*`.

Explicit sidebar filters are ANDed with generated filters using parentheses. Explicit sort takes precedence; relevance leaves room for model-generated ordering. A zero-result final search remains an empty state, rather than silently dropping keywords. Both calls use Scout's Typesense engine. Laravel does not run a separate LLM-to-query system or call the AI provider.

Set `DEVSTORE_SEARCH_DEBUG=true` with `APP_ENV=local` to show the collapsed **Search details** inspector. It shows request count, original request, generated parameters, executed parameters and timings, excluding credentials and raw model responses. Outside local development keep `APP_DEBUG=false` and `DEVSTORE_SEARCH_DEBUG=false`. Read the [experiment notebook](docs/experiment.md) for actual observations and limitations.

## Troubleshooting

| Symptom | Check |
| --- | --- |
| Search unavailable | Typesense health, host/port/protocol, API key and import command; clear Laravel's config cache after environment changes. |
| NL Search unavailable | Register the configured model ID, verify provider model access/quota, and inspect private Typesense logs. Traditional Search needs no AI provider key. |
| Empty interpretation or HTTP 408 | Model/provider failure or timeout; the app rejects empty metadata. The NL server timeout is 60000 ms. |
| Valid search has no hits | Reset explicit filters, distinguish keywords from a sentence, and inspect the generated/executed parameters locally. Do not assume the model interpreted intent correctly. |
| SQLite file missing | Create `database/database.sqlite` and run `php artisan migrate --seed`. A custom `DB_DATABASE` must point to an existing absolute file path. |
| Vite manifest missing | Run `npm ci` and `npm run build`, or keep `npm run dev` running while developing. |
| Artisan server won't accept connections on Windows | Use Herd, or run `php -S 127.0.0.1:8000 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php` **from `public/`**. |

## Development and contribution

```sh
composer validate --strict
php vendor/bin/pint --test
composer test
npm run build
composer audit
npm audit
```

The deterministic Pest suite uses in-memory SQLite and mocked search/provider boundaries. It requires no running Typesense or AI API key and makes no paid model calls. The GitHub Actions workflow runs formatting, tests and the asset build. Record real NL experiments separately; do not write tests that expect an LLM to return a fixed interpretation.

See [CONTRIBUTING.md](CONTRIBUTING.md), [SECURITY.md](SECURITY.md) and the [MIT license](LICENSE). `.env`, SQLite files, logs, private setup files, dependency directories and build output are excluded from Git. Share the source repository or screenshots of synthetic catalog searches; this local experiment has no authentication or production hosting setup.
