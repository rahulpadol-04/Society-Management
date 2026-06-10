# CommunityOS — Testing

## Running

```bash
php artisan test                 # full suite
php artisan test --filter=Billing
vendor/bin/phpunit tests/Feature/Complaints
```

PHPUnit is configured (`phpunit.xml`) to use a disposable database. By default that's `sqlite :memory:` (requires the `pdo_sqlite` extension); to run against MySQL instead, export the DB env before the command:

```bash
APP_ENV=testing DB_CONNECTION=mysql DB_DATABASE=communityos_test \
  php artisan test
```

Every feature test uses `RefreshDatabase` and the shared helpers in `tests/TestCase.php`:

- `seedPlatform()` — builds the RBAC catalogue + global roles + subscription plans.
- `makeSociety($name, $email)` — registers a tenant (society + admin + Enterprise plan) and binds it as the current tenant.
- `admin($society)` / `makeUser($society, $role)` — actors for authorization assertions.

## Coverage (100+ tests, all green)

Every module ships a feature test. The recurring assertions across modules are:

1. **Happy-path create** — the primary action works and persists correctly (e.g. a complaint gets a `CMP-…` reference + a timeline entry; bill generation produces one bill per flat with GST applied; a balanced journal entry posts).
2. **Tenant isolation** — data created in society A is invisible to society B (verified through the global scope; isolation tests call `flushSession()` before the cross-tenant request so flash messages don't leak across the shared test session).
3. **RBAC enforcement** — a role lacking the permission gets `403` (e.g. a resident cannot generate bills, approve a visitor pass, assign a ticket, delete a facility, or open the platform `/societies`).
4. **Business rules** — domain invariants hold (unbalanced journal entries are rejected; trial balance balances; double-booking a facility slot is rejected; overdue bills accrue late fees; a resident can vote on a poll only once; payroll net = basic + allowances − deductions).

Module suites: `tests/Feature/{Complaints,Structure,Residents,Visitors,Facilities,Notices,Maintenance,Accounting,Vendors,Assets,Staff,Helpdesk,Communication,Reports,Settings,SuperAdmin}`.

## Manual QA harness

A route crawler logs in as each role (Super Admin, Society Admin, Accountant, Guard, Maintenance, Resident) and requests every `GET` route, asserting no `5xx`/`419`. This catches Blade/eager-loading regressions that unit tests miss (strict mode is enabled outside production, so any accidental lazy-load throws). Pages were additionally screenshot-verified via headless Chrome.

## Notes for contributors

- `Model::shouldBeStrict()` is on outside production — **eager-load every relation a Blade view touches** or the request 500s.
- Cross-module references are soft links (no FK) — assert against them with plain ids, not relations.
- New modules: copy the structure of an existing `tests/Feature/<Module>/<X>Test.php` and cover the four assertion classes above.
