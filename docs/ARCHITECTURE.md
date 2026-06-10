# CommunityOS — Architecture

## 1. Overview

CommunityOS is a multi-tenant SaaS. One Laravel application + one MySQL database serve every society (tenant). Tenants are isolated at the row level by a `society_id` foreign key on every tenant-scoped table. The platform owner (Super Admin) operates across all tenants.

```
                       ┌─────────────────────────────────────────────┐
   Browser / SPA  ───▶ │  Routes ─▶ Middleware ─▶ Controllers         │
   Flutter app    ───▶ │             (tenant, subscription, feature,  │
   (Sanctum API)       │              role, permission)               │
                       │                    │                          │
                       │                    ▼                          │
                       │   FormRequest (validate + authorize)          │
                       │                    │                          │
                       │                    ▼                          │
                       │   Service (business logic, DB transactions) ──┼─▶ Events ─▶ Queued Listeners ─▶ Notifications
                       │                    │                          │
                       │                    ▼                          │
                       │   Repository (Eloquent, tenant-scoped)        │
                       │                    │                          │
                       │                    ▼                          │
                       │   Model (BelongsToTenant global scope) ───────┼─▶ MySQL (shared DB, society_id)
                       └─────────────────────────────────────────────┘
```

## 2. Multi-tenancy

**Strategy:** shared database, shared schema, row-level isolation (`config/tenancy.php`).

- **`App\Support\Tenancy\TenantManager`** (singleton, alias `tenancy`) holds the current `Society`. Supports `withoutScope()` (Super Admin / system jobs), `forTenant($society, $cb)` (run a closure as another tenant — used by cron jobs), and tenant-namespaced cache keys.
- **`App\Models\Concerns\BelongsToTenant`** — drop-in trait. On boot it adds `TenantScope` (a global Eloquent scope filtering `where society_id = <current>`) and a `creating` hook that stamps `society_id` automatically. So application code never has to remember to filter or set the tenant.
- **`App\Models\Scopes\TenantScope`** — no-ops when tenancy is suppressed or unresolved, so platform code sees across societies.
- **`App\Http\Middleware\IdentifyTenant`** — resolves the tenant per request. Strategy is configurable (`TENANCY_RESOLVER`): `auth` (from the user's `society_id`, default), `subdomain` (`{slug}.domain`), or `header` (`X-Society-Id`, for machine-to-machine APIs). Super Admins are not bound to a tenant unless they impersonate one.
- **Central tables** (never tenant-scoped): `societies`, `subscription_plans`, `subscriptions`, `users`, `roles`, `permissions`, `cms_pages`, `blogs`, `contact_inquiries`, `payment_gateways`, etc.

**Cross-module references** use *soft links* — a plain `unsignedBigInteger` column with an index but **no** foreign-key constraint (e.g. `complaints.flat_id`). This keeps modules independently migratable and avoids cross-module FK ordering problems. Within a module, real FK constraints are used.

## 3. Service–Repository pattern

- **Repositories** (`App\Repositories\Eloquent\*Repository` implementing `Contracts\*RepositoryInterface`) encapsulate persistence. `BaseRepository` provides `paginate/find/create/update/delete` plus declarative `$filterable` / `$searchable` filtering. `RepositoryServiceProvider` binds each interface to its implementation **by naming convention** — no central registration.
- **Services** (`App\Services\<Module>\*Service` extending `BaseService`) hold business logic, wrap multi-step writes in `DB::transaction`, generate human references (e.g. `CMP-`, `INV-`, `TKT-`), and dispatch domain events.
- **Controllers** stay thin: resolve the request, call the service, return a view (web) or an `ApiResponse` envelope (API).

## 4. Dynamic RBAC

`config/communityos.php` declares **roles**, **modules** and the **permission matrix**. A permission slug is `"{module}.{ability}"` (e.g. `complaints.create`).

- **`PermissionRegistrar`** builds the permission catalogue, the global Super Admin role, and per-society copies of every society role with sensible least-privilege defaults (a module's `roles` entry may grant a role *all* abilities or an explicit subset, e.g. `'resident' => ['view','create']`).
- **`HasRoles`** trait on `User` resolves effective permissions (roles ∪ direct grants), cached per user/tenant and invalidated on change.
- **`AuthServiceProvider`** registers a `Gate::before` that grants Super Admins everything, then defines every `"{module}.{ability}"` as a Gate ability backed by the user's dynamic permission set — so `@can('complaints.create')` and policies work everywhere with zero per-module glue.
- **Policies** (auto-discovered by Laravel's `Model → ModelPolicy` convention) authorize per-record actions and allow owners to view their own records.
- **Middleware:** `role:`, `permission:`, and `feature:` guard routes; `EnsureSubscriptionActive` blocks expired tenants (Super Admins and billing pages exempt).

## 5. Plan feature gating

Each module may declare a `feature` flag. A society's subscription plan stores an array of enabled features. `EnsureFeatureEnabled` (`feature:billing`) and `feature_enabled()` gate access; the sidebar hides modules the plan doesn't include.

## 6. Async & scheduled work

- **Events → queued listeners → notifications**: domain events (e.g. `ComplaintCreated`, `VisitorCheckedIn`, `PaymentReceived`, `NoticePublished`, `FacilityBooked`, `TicketAssigned`) are handled by `ShouldQueue` listeners on the `notifications` queue, which fan out mail + database notifications.
- **Queue jobs** (`app/Jobs/...`): `Maintenance\GenerateMonthlyBills`, `Maintenance\ApplyLateFees`, `Maintenance\SendDueReminders`, `Communication\DeliverBroadcast`. Cross-tenant jobs iterate societies via `tenancy()->forTenant(...)` so the global scope and `society_id` stamping apply per society.
- **Scheduler** (`routes/console.php`): monthly bill generation (1st @ 02:00), nightly late fees (03:00), due reminders (09:00), SLA escalation (hourly), plus prune tasks. Driven by a single system cron entry running `php artisan schedule:run`.

## 7. Request lifecycle (web, authenticated)

`auth` → `IdentifyTenant` (bind tenant) → `EnsureSubscriptionActive` → per-module `feature:` / `permission:` → controller → `FormRequest::authorize()` (policy/permission) → service → repository → model (tenant scope) → Blade view (`layouts.app` + config-driven sidebar).

## 8. Front-end

Server-rendered Blade + Bootstrap 5. `public/js/app.js` wires CSRF into jQuery AJAX, auto-initialises DataTables (skipping empty tables), renders Chart.js widgets from `/dashboard/chart/{type}` JSON into fixed-height containers, and drives the dark/light theme toggle (persisted in `localStorage`). `public/css/app.css` is the theme (blue accent, dark navy sidebar, soft cards, stat tiles), theme-aware via Bootstrap 5.3 `data-bs-theme`.

## 9. Extending: add a module in ~10 files

1. Add the module + abilities to `config/communityos.php`.
2. Migration (`society_id` + soft links), Model (`BelongsToTenant`), Repository + Contract, Service, FormRequests, Policy, web + API Controllers, Resource.
3. `routes/web/<module>.php` and `routes/api/<module>.php` (auto-loaded; expose `<module>.index`).
4. Views under `resources/views/<module>/`, a `Modules/<Module>Seeder`, a factory and a feature test.

RBAC, the sidebar entry, Gates, policy discovery, repository binding and seeder discovery all wire up automatically from config + conventions.
