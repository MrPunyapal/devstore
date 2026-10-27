# Contributing

Follow the [README](README.md) to install the application. Keep changes focused on the catalog and search experiment. This project intentionally has no authentication, checkout, admin panel or application API.

Before opening a pull request, run:

```sh
composer validate --strict
php vendor/bin/pint --test
composer test
npm ci
npm run build
```

The tests use in-memory SQLite and mocked search/provider boundaries. They do not require a Typesense server or paid Gemini calls. Record real search observations in `docs/experiment.md` with the exact query, explicit filters, model, server version and returned parameters. Preserve earlier observations when the dataset changes. Do not assert that an LLM must always return one specific interpretation.

For bug reports, include reproduction steps, dependency/server versions and the sanitized query parameters. Leave API keys, `.env` files, private configuration and raw server/model responses out of issues, screenshots and pull requests. Use synthetic queries rather than private user data.

If you change the search schema, describe the indexing impact. Test collection recreation only on your own disposable Typesense instance; importing alone does not migrate an existing collection schema.
