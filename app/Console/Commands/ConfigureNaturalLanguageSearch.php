<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ConfigureNaturalLanguageSearch extends Command
{
    protected $signature = 'devstore:configure-nl {--model=google/gemini-3.1-flash-lite : Gemini model name in Typesense provider/model format}';

    protected $description = 'Register a Gemini NL model in Typesense using a hidden API-key prompt';

    public function handle(): int
    {
        $settings = config('scout.typesense.client-settings');
        $node = $settings['nodes'][0];
        $id = config('devstore.nl_model_id');

        if (empty($settings['api_key']) || empty($id)) {
            $this->error('Set TYPESENSE_API_KEY and TYPESENSE_NL_MODEL_ID in .env first.');

            return self::FAILURE;
        }

        $model = $this->option('model');
        if (! is_string($model) || ! str_starts_with($model, 'google/') || strlen($model) <= 7) {
            $this->error('Use a Gemini model name such as google/gemini-3.1-flash-lite.');

            return self::FAILURE;
        }

        $base = rtrim($node['protocol'].'://'.$node['host'].':'.$node['port'].($node['path'] ?? ''), '/');
        $http = Http::acceptJson()->connectTimeout(5)->timeout(60)
            ->withHeaders(['X-TYPESENSE-API-KEY' => $settings['api_key']]);

        try {
            $existing = $http->get($base.'/nl_search_models/'.rawurlencode($id));
            if ($existing->successful()) {
                $this->info('Typesense NL model already exists; its configuration was left unchanged.');

                return self::SUCCESS;
            }
            if ($existing->status() !== 404) {
                $this->error('Typesense model lookup failed (HTTP '.$existing->status().'). Check the connection and key permissions.');

                return self::FAILURE;
            }

            $key = $this->secret('Gemini API key');
            if (! is_string($key) || trim($key) === '') {
                $this->error('A Gemini API key is required. Nothing was registered.');

                return self::FAILURE;
            }

            $response = $http->post($base.'/nl_search_models', [
                'id' => $id,
                'model_name' => $model,
                'api_key' => trim($key),
                'max_bytes' => 16000,
                'temperature' => 0.0,
            ]);

            // Typesense model responses can contain provider credentials: never print the body.
            if (! $response->successful()) {
                $this->error('Typesense model registration failed (HTTP '.$response->status().'). Check the selected model and Typesense logs.');

                return self::FAILURE;
            }
        } catch (ConnectionException) {
            $this->error('Could not reach Typesense. Check its health endpoint and the TYPESENSE_* connection settings.');

            return self::FAILURE;
        }

        $this->info('Registered Typesense NL model. The Gemini key was sent to Typesense and was not saved in this project.');

        return self::SUCCESS;
    }
}
