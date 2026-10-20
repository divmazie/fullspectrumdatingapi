# Matching tests

Scripts that pin down how matching behaves, so a new implementation (see #15) can be checked against the current one. They were used to verify #20, which moved matching from Python to SQL (`Matcher.php`).

| Script | What it does |
|---|---|
| `gen_profiles.php` | Adds random profiles to a test database |
| `compare_matches.php` | Calls `profiles/get-matches` on two APIs and checks the scores, matches and top dimensions agree |
| `norms_and_concurrency.php` | Checks stored vector lengths stay correct on save, and that simultaneous first loads don't create duplicate rows |
| `bench.php` | Times `profiles/get-matches` |

**Never point these at the production database.** The generator creates sessions with predictable tokens, so anyone could sign in as the generated profiles. Scripts that write refuse to run unless the database name contains `test`.

## Setup

1. Create a test database and load the schema and seed data:
   ```sh
   mysql -e "CREATE DATABASE fsd_test"
   cat sql/*.sql sql/data/*.sql | mysql fsd_test
   ```
2. Point a `config.php` at it, with the same keys as the server's. The scripts read the repo's `config.php`, or the file named in the `MATCHING_CONFIG` environment variable.
3. Add profiles. Arguments are the count, a random seed and the fraction of dimensions answered (default 0.15):
   ```sh
   php tests/matching/gen_profiles.php 300 7
   ```
4. Serve the API from this machine. The generated sessions only accept requests from `127.0.0.1`.
   ```sh
   php -S 127.0.0.1:8102 -t .
   ```

## Comparing two implementations

Serve the reference (for example a checkout of `master`) and the candidate side by side, both using the same test database, then:

```sh
php tests/matching/compare_matches.php http://127.0.0.1:8101/index.php http://127.0.0.1:8102/index.php
```

- Without viewer ids, it picks a mix: profiles with no answers, each `nyc` setting, and some at random.
- Scores must agree to within 1e-6. Sliders are stored at single precision, so expect differences around 1e-8.
- Top identities and preferences are checked against their definition: the 3 strongest positive answers, ties by row id.

When the candidate is a new API, change `fetch()` in `compare_matches.php` to use its request format and authentication.

## Concurrency and benchmarks

```sh
PHP_CLI_SERVER_WORKERS=8 php -S 127.0.0.1:8103 -t .
php tests/matching/norms_and_concurrency.php http://127.0.0.1:8103/index.php

php -d memory_limit=128M -S 127.0.0.1:8104 -t .
php tests/matching/bench.php http://127.0.0.1:8104/index.php
```

`norms_and_concurrency.php` needs a server with several workers, so that its simultaneous requests really overlap.

For reference, from #20 (MariaDB, PHP 8.3): ~0.15 s per request at 1,000 profiles and ~1.2–1.6 s at 10,000. At 10,000 profiles, most of that time goes into building the 8 MB response containing every match.
