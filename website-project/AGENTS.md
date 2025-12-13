# AGENTS (for automated contributors)

This document gives precise rules for automated agents or contributors making changes to this repository.

Rules summary (apply automatically):

- Keep changes small and reversible. Prefer a single purposeful commit per change.
- When changing API contracts (any `/api/*` endpoint), update the matching frontend JS in `public/js/*` and add or update a unit test that exercises the changed logic.
- Tests: unit tests are required for logic changes. Use the provided `phpunit.xml` and `tests/bootstrap.php` (the test bootstrap uses `DB_DSN=sqlite::memory:`). New tests should not rely on an external MySQL instance.
- Database credentials: do not hard-code credentials into code. Use environment variables (`DB_DSN` or `DB_HOST`/`DB_NAME`/`DB_USER`/`DB_PASS`). Update `.env.example` when adding new env variables.
- If you propose migration to PSR-4 / namespaces, provide a minimal example conversion (one controller + one model) with tests. Do not mass-rename files without a migration plan.
- Security: do not replace demo auth with production-ready tokens or external services without an explicit request; instead add a documented migration path (e.g., swap demo token for JWT + token store).

How to run tests locally (agent-friendly):

```bash
composer install
./vendor/bin/phpunit --colors=always
```

When creating a PR, include:
- Summary of changes and why.
- Files changed list.
- Tests added/updated and test results.
- Any required manual steps (DB migration, .env updates).

If anything here is unclear, ask for human review before merging.
