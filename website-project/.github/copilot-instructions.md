<!-- .github/copilot-instructions.md
Instructions to help AI coding agents be productive in this repository.
Keep this file concise and focused on project-specific patterns, run steps, and integration points.
-->

# Copilot instructions for Website Project

Short, actionable guidance for code generation and edits. Use this to produce changes that fit the project's structure and conventions.

- Project type: small PHP + static frontend application. Backend is organized under `src/` (PSR-4 autoloading present in `composer.json`). Frontend lives in `public/`.
- Key entry points:
  - API router: `src/api/api.php` — lightweight PHP router for API endpoints (used by AJAX calls from `public/js/*`).
  - Controller classes: `src/controllers/*.php` — one controller per resource (e.g., `ListingController`, `UserController`, `AuthController`).
  - Models: `src/models/*.php` — simple ActiveRecord-style static methods (e.g., `Listing::search`, `User::create`).
  - Database: `src/config/database.php` — singleton `Database::getInstance()` returns a PDO instance configured for MySQL.

- Run / dev commands (verified from repo):
  - Install PHP dependencies: `composer install`
  - Run a local server (serves `public/`):

    ```bash
    php -S localhost:8000 -t public
    ```

  - Run tests (if present): `composer test` (maps to `phpunit` in `composer.json`).

- Architectural notes and conventions (important for edits):
  - Controllers generally echo JSON and expect to be included from `src/api/*.php` routes. Keep responses as JSON and set `Content-Type: application/json` in APIs.
  - Models use `Database::getInstance()` and raw PDO prepared statements. Prefer parameterized queries and preserve the existing simple pattern (no ORMs).
  - Authentication is minimal: `AuthController::login` generates a random token for demo only. Do not replace with production JWTs unless explicitly requested.
  - File paths in `src/api/*.php` and `src/controllers/*` use relative includes (e.g., `require_once __DIR__ . '/../controllers/ListingController.php'`). When moving or renaming files, update those includes.

- Error handling & status codes:
  - Existing API handlers return JSON and set HTTP status codes in some places (e.g., 404, 405). When adding new handlers, mirror this style: return machine-readable JSON and set appropriate `http_response_code()`.

- Examples to follow when generating code:
  - Add a GET listings endpoint: follow `src/api/listings.php` pattern — include the model, call `Listing::search($_GET)`, echo json_encode and exit.
  - Create a model method: follow `src/models/Listing.php` style — accept `$params`, call `Database::getInstance()`, prepare SQL, execute with `$args`, return fetch/fetchAll.

- Tests and dev-safety:
  - Keep changes small and testable; use the built-in server to manually exercise endpoints.
  - If adding dependencies, update `composer.json` and call out the required `composer require` or `composer update` in the PR description.

- When editing: preferred places to modify for feature work
  - Route additions: `src/api/api.php` or create a dedicated `src/api/*.php` file mirroring existing ones (e.g., `src/api/listings.php`).
  - Business logic: `src/controllers/*` and `src/models/*` — keep responsibility separation (controllers orchestrate, models access DB).

- Cross-component notes:
  - Frontend `public/js/*.js` expects JSON endpoints under `/api/*` (search files like `public/js/search.js` when changing API contract).
  - `database/schema.sql` contains DB schema — update when adding or changing columns.

- Non-obvious conventions discovered:
  - Models use static methods rather than instance objects.
  - No centralized dependency injection — Database singleton and procedural includes are used.
  - Auth is demo-grade; tokens are randomly generated strings stored nowhere.

If anything in this file seems incomplete or you want the agent to follow a stricter policy (tests, logging, JWTs), say which areas to expand.
