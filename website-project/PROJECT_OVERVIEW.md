# Project Overview — HouseHunting

This document summarizes the code structure, API routes, important files, environment requirements, and next steps to get the project fully loading real content (listings, images, videos).

## Repo layout (important folders)
- `public/` — Frontend static files, HTML pages and client-side JS under `public/js/` (single-page-ish behavior).
- `src/` — PHP backend code (Controllers, Models, Helpers, Config).
- `database/` — SQL schema and seeders.
- `vendor/` — Composer dependencies.

## Entry points
- `public/index.php` — Main API router using `Bramus\Router`. Base path for API is `/api`.
- `public/*.html` — Static frontend pages.

## Key backend pieces
- `src/Controllers/` — Controllers for routes (AuthController, ListingController, UserController, PaymentController, etc.).
- `src/Models/` — Data access (BaseModel, Listing, Image, User, Reservation, Video, ...).
- `src/Helpers/` — Utility helpers (Request, JwtMiddleware, View, etc.).
- `src/Config/Config.php` and `DatabaseConnection.php` — env loader and DB connection.

## API routes (high-level)
All API routes are under `/api` (see `public/index.php`). Highlights:
- Public: `GET /` (home), `POST /register`, `POST /login`, `GET /listings/search`, `GET /cities`, `GET /listings/{id}`, etc.
- Authenticated (`/users/*`): `GET /users/me`, `PUT /users/me`, `GET /users/me/favorites`, `PUT /users/me/favorites/{id}`, `DELETE /users/me/favorites/{id}`, `GET /users/me/searches`, `GET /users/me/reservations`.
- Owner/Admin routes protected by JWT and role checks.

## What I changed so far (summary)
- Added `src/Controllers/UserController.php` implementing user endpoints (me, favorites, saved searches, reservations).
- Replaced `src/Helpers/JwtMiddleware.php` stub with a JWT decoder that validates Bearer tokens using `JWT_SECRET` from `.env`. Keeps a development fallback when `APP_ENV` is `local|dev|development`.
- Made images handling robust:
  - `src/Models/Image.php` now supports schemas that use `path` or `image_path` column names, provides `findByListingId` and `findByListingIds`, and normalizes returned paths by prefixing `images/` when filenames are stored without folders.
  - `src/Models/Listing.php` now fetches images correctly for search results via `Image::findByListingIds`.
  - `src/Controllers/ListingController.php` now returns detailed listing payloads (images, neighborhood, videos) via `Listing::findByIdWithDetails`.
  - Added `src/Models/Video.php` and made `Listing::findByIdWithDetails` include videos.
  - Small fixes to `ListingController` image creation to use `path` key (Image::create adapts to DB schema).

## How listings & images now load (what's working)
- The frontend `public/js/listings.js` fetches listings from `/api/listings/search` and expects responses `{ data: [...], total: N }`. Backend `Listing::search()` returns that shape and now includes `images` arrays on each listing.
- Each image object returned has a `path` property (or the JS `utils.getImageUrl` will read `image_path` or `path`). If your DB stores just filenames, the model will return `images/filename` so the client can load `public/images/filename`.
- The listing details page (`public/js/listing-details.js`) fetches `/api/listings/{id}` and now receives images, amenities (currently empty), videos (from `videos` table), and neighborhood.

## Environment variables (important)
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — database connection.
- `JWT_SECRET` — secret used to sign/verify tokens. When missing and `APP_ENV` is `development` a dummy user is used.
- `OPENAI_API_KEY`, `STRIPE_SECRET_KEY`, `PAYPAL_*`, `MPESA_*` — optional third-party services used by features.

## DB Schema notes & seeding images
- The canonical schema is in `database/schema.sql`. Note: historically there were mixed column names for images (`path` vs `image_path`). The code now handles either.
- To seed images into the DB from `public/uploads`, run the script `php setup_images.php` (it will assign images to listings). Alternatively run database seeders in `database/seeders/`.

## Remaining work & recommended next steps
1. Payment flows: `src/Controllers/PaymentController.php` uses an ActiveRecord-like pattern (->save()) and references a `Payment` model that doesn't exist; this area needs rewriting to use `BaseModel` patterns or add proper models and table (`payments`).
2. Replace remaining ActiveRecord-style usages in some controllers (e.g., Reservation, Payment) with `BaseModel` static methods or add ORM helpers.
3. Add unit/integration tests for core endpoints (listings search, listing details, user favorites).
4. Frontend polishing: ensure pages use server-supplied user data (not only localStorage), add loading states and error handling.
5. Improve amenities loading: implement `Amenity` model joins in `Listing::findByIdWithDetails`.

## How you can run locally (quick)
1. Copy `.env.example` to `.env` and update DB credentials and `JWT_SECRET`.
2. Import the SQL schema: `mysql -u user -p househunting < database/schema.sql` (or use phpMyAdmin).
3. Run composer install (if dependencies missing): `composer install`.
4. Seed DB (optional): `php database/seed.php` and `php setup_images.php` to assign images.
5. Serve the `public/` folder via your webserver (Apache, XAMPP, etc.) and open `index.html`.

## If you'd like, I can next
- Run the linters and tests and fix errors I can automatically (quick sweep).
- Rewrite `PaymentController` and add a proper `Payment` model so payments are functional.
- Wire frontend to use server-returned user object after favorite toggles and implement optimistic updates reliably.

If you want me to continue, say which next steps to prioritize (tests, payments, frontend wiring, styling), or say `do all` and I'll proceed in that order and keep you updated.
