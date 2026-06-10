# CommunityOS

**A production-grade, multi-tenant Society & Apartment Management SaaS** built with Laravel 12, MySQL 8, Blade + Bootstrap 5, jQuery, DataTables and Chart.js. Designed for residential communities, gated societies, apartment complexes and property-management companies — scalable to 1000+ societies on a shared-database, row-level-isolation architecture.

> A companion **Flutter mobile app** lives in a separate folder: `../communityos_mobile`.

---

## ✨ Features

### Platform (Super Admin)
Manage all societies · subscription plans (trial / monthly / quarterly / annual) · per-plan feature restrictions · subscriptions & invoices · revenue dashboard & tenant usage analytics · payment-gateway settings · CMS pages · blog · contact inquiries · society impersonation.

### Society (per-tenant) modules
| Group | Modules |
|-------|---------|
| **Society** | Society profile · Towers / Blocks / Floors / Flats-Units · Parking slots · Documents |
| **People** | Residents · Family members · Tenants · Vehicles · Emergency contacts · Resident directory |
| **Operations** | Visitor management (entry/exit, QR passes, approval, pre-approved, delivery/cab, guard gate console) · Complaints (SLA, assignment, timeline, feedback) · Facility booking (clubhouse/gym/pool/court/hall, slots, approval) · Notice board (notices/announcements/circulars/events/polls + voting) · Assets (categories, depreciation, maintenance schedules) |
| **Finance** | Maintenance billing (heads, area/flat-type charges, GST, late fees, invoices, receipts, due reports) · Accounting (chart of accounts, journal entries, trial balance, P&L, balance sheet, bank/cash accounts) · Vendors (contracts, work orders, payments, ratings) |
| **Engagement** | Communication (email/SMS/WhatsApp/push broadcasts, templates, internal messaging) · Helpdesk (tickets, internal notes, escalation matrix, SLA) · Reports (visitor/billing/collection/complaint/facility/occupancy/financial — CSV + print/PDF) · Settings master (general, billing config, roles & permissions) |

### Cross-cutting
Dynamic, configurable **RBAC** (10 roles, per-society permission matrix) · **Laravel Sanctum** API (token + SPA) · queue jobs & event-listener side-effects · audit logs · login history · 2-factor auth (TOTP) · password policies · API rate limiting · CSRF · Chart.js dashboards · DataTables · dark/light theme.

---

## 🧱 Architecture at a glance

- **Multi-tenancy:** shared database, row-level isolation. Every tenant table carries `society_id`; the `BelongsToTenant` trait applies a global Eloquent scope (read isolation) and auto-stamps `society_id` on insert (write isolation). The `TenantManager` singleton holds the current tenant; the `IdentifyTenant` middleware resolves it (auth / subdomain / header). Super Admins operate cross-tenant and can impersonate.
- **Service–Repository pattern:** controllers → services (business logic, transactions, events) → repositories (persistence). Repository contracts auto-bind to Eloquent implementations by naming convention.
- **Config-driven RBAC & navigation:** `config/communityos.php` is the single source of truth for roles, modules, permissions and feature flags. The sidebar, Gates, policies and seeders all read from it — adding a module is a one-line change.
- **Plan feature gating:** the `feature:<flag>` middleware + `feature_enabled()` helper gate modules by the society's subscription plan.
- **Events → queued listeners → multi-channel notifications:** e.g. a new complaint dispatches `ComplaintCreated`; a queued listener notifies the right people via mail + database channels.

See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) (full write-up), [docs/DATABASE.md](docs/DATABASE.md) (schema/ER), [docs/API.md](docs/API.md) (REST API), [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) (AWS, queues, cron) and [docs/TESTING.md](docs/TESTING.md).

---

## 🚀 Local setup

**Requirements:** PHP 8.3+, Composer, MySQL 8, Redis (optional in dev). Front-end assets are CDN-loaded (no Node build needed).

```bash
composer install
cp .env.example .env            # set DB_*, then:
php artisan key:generate

# create the `communityos` database, then:
php artisan migrate --seed       # schema + demo society + demo data
php artisan storage:link         # public disk for uploads

php artisan serve                # http://localhost:8000
php artisan queue:work           # process queued notifications/jobs (separate terminal)
```

### Demo accounts (password: `Password@123`)

| Role | Email |
|------|-------|
| Super Admin | `super@communityos.io` |
| Society Admin | `admin@greenvalley.test` |
| Accountant | `accountant@greenvalley.test` |
| Security Guard | `guard@greenvalley.test` |
| Maintenance Staff | `maintenance@greenvalley.test` |
| Resident | `resident@greenvalley.test` |

The seeder provisions a demo society (**Green Valley Residency**) populated across every module, plus two extra societies for the platform views.

---

## ✅ Tests

```bash
php artisan test
```

100+ feature tests cover tenant isolation, RBAC, the billing engine, accounting balance rules, the visitor/complaint/helpdesk lifecycles, the settings master and the platform panel. See [docs/TESTING.md](docs/TESTING.md).

---

## 📁 Folder structure (high level)

```
app/
  Http/Controllers/{<Module>,Api/<Module>,SuperAdmin,Auth}/   web + API controllers
  Http/Middleware/         IdentifyTenant, EnsureSubscriptionActive, EnsureFeatureEnabled, CheckRole, CheckPermission
  Http/Requests/<Module>/  form requests (validation + authorization)
  Models/                  Eloquent models (+ Concerns: BelongsToTenant, Auditable, HasRoles)
  Services/<Module>/       business logic (extend BaseService)
  Repositories/{Contracts,Eloquent}/   service-repository pattern
  Policies/                per-model authorization
  Events/ Listeners/ Notifications/ Jobs/   async side-effects
  Support/Tenancy/         TenantManager + helpers
config/communityos.php     roles · modules · permissions · plan features · billing defaults
database/migrations|seeders|factories/
resources/views/<module>/  Blade UI (layouts, partials, per-module)
routes/{web,api}.php + routes/{web,api}/<module>.php   auto-loaded per-module route files
public/css/app.css  public/js/app.js   theme + DataTables/Chart.js helpers
```

---

## 🛠️ Tech stack

Laravel 12 · PHP 8.3 · MySQL 8 · Laravel Sanctum · Laravel Queues · Redis cache · AWS S3 (filesystem) · Blade · Bootstrap 5 · jQuery · AJAX · DataTables · Chart.js · Flutter (mobile).
