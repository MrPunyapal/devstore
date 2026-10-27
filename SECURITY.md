# Security

DevStore is a local search experiment with no authentication or production deployment setup. Keep the application and Typesense on localhost for experimentation. Queries and schema metadata used for Natural Language Search are sent by Typesense to the configured AI provider. Provider access, quotas and billing belong to the person running the demo.

## Credentials

- Laravel's `TYPESENSE_API_KEY` authorizes server-side access to Typesense. It is separate from any AI provider credentials.
- AI provider credentials belong in the Typesense NL model configuration. Gemini is optional; `php artisan devstore:configure-nl` is a Gemini-specific helper that prompts for its key without echoing it, sends it to the configured Typesense server, and does not save it in this checkout. Configure other supported providers directly in Typesense.
- `.env`, private setup files, SQLite databases, server data and logs are ignored by Git. Do not force-add them. Use a new Typesense key for your own installation, and rotate any credential you accidentally disclose.
- Keep `APP_DEBUG=false` and `DEVSTORE_SEARCH_DEBUG=false` outside a private local environment. Laravel's debug exception pages can reveal configuration. Typesense model-management responses can include provider configuration; do not paste them into issues.

## Reporting a vulnerability

Use the repository's **Security → Report a vulnerability** option if the maintainer has enabled private vulnerability reporting. Do not post credentials or an unpatched exploit containing private data in a public issue. If private reporting is unavailable, ask the maintainer for a private reporting channel without including the sensitive details.

Before sharing a fork, run `composer audit` and `npm audit`, review the files Git will include, and check that no local credentials or data are present. The MIT license does not provide a production security guarantee.
