# DevStore experiment

## Question

Can Typesense's built-in Natural Language Search turn hardware requests into useful keywords, numeric constraints, category filters and ordering on this small catalog? Compare the returned parameters and products with an explicit traditional query. This is a technical experiment, not an assessment of all LLM search systems.

## Dataset

292 illustrative catalog entries: 100 laptops, 36 monitors, 33 keyboards, 33 mice, and 30 each of headphones, webcams and docking stations. Named hardware families have RAM/storage configurations or clearly marked illustrative accessory kits. Prices are INR per item; ratings, stock status, dates and configurations are synthetic. They are not verified specifications or retailer offers. Null fields mean not applicable, not zero. `wireless` refers to peripheral connectivity, not laptop Wi-Fi. RAM and storage filters apply to laptops. Earlier observations below used the original 240-item dataset; those counts are retained as historical measurements.

## Comparison

Traditional Search sends keywords with explicit `filter_by` and `sort_by` parameters. Natural Language Search sends the original sentence with `nl_query=true` and the ID of a model registered in Typesense. Typesense calls the configured model and executes its interpretation; Laravel does not translate intent or contact an LLM provider. Sidebar constraints remain explicit and are combined with generated constraints by Typesense. The inspector shows the generated and augmented parameters so conflicts can be investigated.

Relevance leaves `sort_by` unset, allowing Typesense's default ranking or an NL-generated order. Choosing another sort supplies an explicit order; record the augmented order to see the actual precedence. Empty queries browse all items without making an NL call. NL requests are submitted deliberately; filter and pagination changes rerun the submitted request and may invoke the model again.

## Queries to test

1. “I need a lightweight laptop for Laravel development under ₹1 lakh with at least 32GB RAM”
2. “Show me a large monitor for coding under ₹50000”
3. “I need a wireless keyboard and mouse setup under ₹15000”
4. “Find me something portable with a good rating and at least 16GB RAM”

For each, record exact input, selected filters/sort, Typesense version, NL model ID/provider version (no credentials), generated parameters, augmented parameters, matching items, parse/search time, failures and any ambiguity. Compare with explicitly chosen numeric thresholds. “Lightweight”, “large” and “good rating” do not have guaranteed interpretations. The catalog returns individual items, not an optimized multi-item basket; query 3 cannot guarantee a keyboard-plus-mouse total budget.

Test conflicts (under ₹50000 in the sentence versus a minimum price of ₹60000), unavailable items, empty results, repeated queries, different currency notation and service/model errors. No test should require the LLM to produce the same interpretation every time.

## Actual observations

2026-10-08 setup check: Typesense 30.2 was reachable and Scout imported all 240 products. The server's NL model list was empty, and retrieving `gemini-model` returned HTTP 404. The first planned NL query returned an error with keyword fallback metadata; DevStore rejected the fallback. No successful NL interpretation has been observed yet.

| Date | Query / mode | Explicit constraints | Generated / augmented parameters | Results / timing | Observation |
| --- | --- | --- | --- | --- | --- |
| 2026-10-08 | `ThinkPad` / Traditional | Category laptops, price ≤ ₹100000, RAM ≥ 32 GB; price ascending | Explicit constraints sent to Typesense | 2 items: ThinkPad T14 Gen 5 32GB / 512GB and 32GB / 1024GB | Returned documents satisfied the category, price and RAM constraints; prices were ascending. |
| 2026-10-08 | Query 1 / Natural Language | None; relevance | No generated parameters; original sentence retained as fallback query | Typesense returned an error and 20 keyword fallback matches; DevStore showed an error and did not present them as NL results | `gemini-model` was not registered. Interpretation remains untested until model setup is completed. |

### After model registration, 2026-10-08

The supplied `gemini-model` now resolves to `google/gemini-3.1-flash-lite`. Real NL calls generated parameters without a reported provider error, but returned zero hits:

- `Find ThinkPad laptops with at least 32GB RAM under ₹100000`: generated `q=ThinkPad` and `category:laptops && ram_gb:>=32 && price:<100000 && in_stock:true`. A direct traditional request using exactly those parameters returned one item: Lenovo ThinkPad T14 Gen 5 — 32GB / 512GB. The stock constraint was added by the model even though the sentence did not request it.
- `Show me wireless keyboards under ₹7000 with a rating of at least 4`: generated `category:keyboards && wireless:true && price:<7000 && rating:>=4` with an empty `q`; zero hits.
- `Show me large monitors for coding under ₹50000`: generated `q=coding`, `category:monitors && price:<50000` and `sort_by=screen_size:desc`; zero hits. The model used size ordering instead of a minimum screen-size threshold.

Both single-search and multi-search returned zero for the ThinkPad sentence. The mismatch between NL and an identical explicit query is consistent with [Typesense issue #2910](https://github.com/typesense/typesense/issues/2910), which reports a 30.x regression in applying the generated query text. This is an inference supported by the observed behavior, not a server-source diagnosis of this particular installation. No server binary or supplied model configuration was changed. At that stage, the inspector demonstrated interpretation only; result comparisons were blocked pending a verified workaround.

## Deterministic validation

The Pest suite checks model serialization, catalog validity, query parameter construction, Livewire state, validation, pagination, escaped output and safe error rendering. UI tests substitute the search boundary to test application behavior; they do not establish NL accuracy. Real server behavior and NL interpretations belong in the observations above.

### Verified two-request application workaround, 2026-10-08

DevStore now requests an interpretation from the supplied Typesense model with `timeout_ms=60000`, then executes the returned query/filter/sort through Typesense without `nl_query`. Explicit sidebar filters are retained with AND; explicit sort overrides generated sort. Empty generated q becomes `*`. Failed/empty interpretation metadata is rejected. Zero hits do not trigger a keyword-dropping retry. The server binary and provider/model configuration were not changed.

The following are actual one-run observations against DevStore's 240-item `products` collection and `gemini-model` (`google/gemini-3.1-flash-lite`), with reset sidebar filters and relevance ordering unless specified. These counts are not guarantees for future LLM calls.

| Query | Found | Generated interpretation | Parse time | Observation |
| --- | --- | --- | --- | --- |
| `ThinkPad` / Traditional, category laptops, maximum price 100000, minimum RAM 32 | 2 | No NL parsing | — | Maximum-price filter is inclusive, so the ₹100000 configuration is included. |
| `Find ThinkPad laptops with at least 32GB RAM under ₹100000` / Traditional, no sidebar constraints | 0 | Full sentence used as keywords | — | Traditional Search does not interpret the sentence as numeric filters. |
| Same ThinkPad sentence / Natural Language | 1 | q=ThinkPad; laptops, RAM >=32, price <100000, in_stock=true | 1480 ms | Lenovo ThinkPad T14 Gen 5 — 32GB / 512GB at ₹93000. Model added a stock constraint not explicitly requested. |
| Original planned query 1: lightweight Laravel laptop under ₹1 lakh with 32GB RAM | 20 | q=Laravel development; laptops, price <100000, RAM >=32; weight ascending | 2982 ms | Lakh converted to 100000. Lightweight interpreted as ordering, not a weight threshold. First displayed item: ASUS Zenbook 14 OLED UX3405 — 32GB / 1024GB, ₹97000, 1.2 kg. |
| Original planned query 2: large monitor for coding under ₹50000 | 22 | q=coding; monitors, price <50000, in_stock=true; screen size descending | 1642 ms | Large interpreted as ordering, not a minimum screen size. First displayed family: Samsung Odyssey G5. |
| Original planned query 3: wireless keyboard and mouse setup under ₹15000 | 20 | q=keyboard mouse setup; wireless=true, categories keyboards OR mice, price <15000; rating descending | 1538 ms | Individual catalog items, not a combined basket or total-budget optimizer. |
| Original planned query 4: portable, good rating, at least 16GB RAM | 41 | q=portable; rating >=4, RAM >=16; rating descending | 1361 ms | Good rating interpreted as >=4. No numeric weight limit generated. |

The local inspector shows `request_count=2` for non-empty NL requests and the parameters actually used for the second search. These observations support that the interpretation and execution path works for this run; they do not establish consistent LLM accuracy.

### Dataset expansion for screenshots, 2026-10-08

Added 52 illustrative configurations across additional ThinkPad, Yoga, TravelMate, ExpertBook, monitor, keyboard and mouse families. Both SQLite and Typesense report 292 records. A real rerun of the ThinkPad NL sentence returned 11 products using q=ThinkPad, laptops, RAM >=32, price <100000 and in_stock=true. A real rerun of the original Laravel laptop request returned 44 products using q=Laravel development, laptops, price <100000, RAM >=32 and weight ascending. No results were fabricated or forced. Earlier counts above refer to the original 240-item catalog.