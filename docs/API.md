# CommunityOS — REST API (v1)

Base URL: `/api/v1` · Auth: **Laravel Sanctum** (Bearer token for mobile/3rd-party, or stateful cookie for the first-party SPA). All routes except `auth/login` require `Authorization: Bearer <token>` and are tenant-scoped to the authenticated user's society. Global rate limit: **60 req/min** per user/IP (`throttle:api`); `auth/login` is throttled to 5/min.

## Response envelope

Every endpoint returns a uniform JSON envelope (`App\Http\Concerns\ApiResponse`):

```jsonc
// success (single)
{ "success": true, "message": "OK", "data": { … } }
// success (paginated)
{ "success": true, "message": "OK", "data": [ … ],
  "meta": { "current_page": 1, "last_page": 5, "per_page": 15, "total": 73 } }
// error
{ "success": false, "message": "…", "errors": { "field": ["…"] } }
```

Common status codes: `200` OK · `201` Created · `401` Unauthenticated · `402` Subscription expired · `403` Forbidden (policy/feature) · `404` Not found · `422` Validation · `429` Rate limited.

## Authentication

```http
POST /api/v1/auth/login
Content-Type: application/json
{ "email": "resident@greenvalley.test", "password": "Password@123", "device_name": "pixel-8" }
```
Returns `{ data: { token, user } }`. Send `token` as `Authorization: Bearer <token>` thereafter.

```http
GET  /api/v1/auth/me        # current user
POST /api/v1/auth/logout    # revoke current token
```

## Dashboard

```http
GET /api/v1/dashboard/chart/{type}
# type: revenue | visitor-trends | complaint-trends | maintenance-collection | occupancy | facility-usage
```

## Resource endpoints

Standard REST resources return the envelope above; list endpoints accept `?search=&sort=&dir=&per_page=` plus per-module filters.

| Module | Endpoints |
|--------|-----------|
| **Complaints** | `GET/POST /complaints` · `GET/PUT/DELETE /complaints/{id}` |
| **Structure** | `GET/POST /flats` · `GET/PUT/DELETE /flats/{id}` |
| **Residents** | `GET/POST /residents` · `GET/PUT/DELETE /residents/{id}` |
| **Visitors** | `GET /passes` · `POST /passes` · `POST /passes/{pass}/approve` · `POST /gate/validate` · `POST /gate/checkin` · `POST /gate/checkout/{log}` |
| **Facilities** | `GET /facilities` · `GET /bookings` · `POST /facilities/{facility}/book` · `POST /bookings/{booking}/approve` · `POST /bookings/{booking}/cancel` |
| **Notices** | `GET /notices` · `GET /notices/{id}` · `POST /polls/{poll}/vote` |
| **Maintenance** | `GET /maintenance/bills` · `GET /maintenance/bills/{bill}` · `POST /maintenance/bills/{bill}/pay` |
| **Accounting** | `GET/POST /accounting/journals` · `GET/PUT/DELETE /accounting/journals/{id}` |
| **Vendors** | `GET/POST /vendors` · `GET/PUT/DELETE /vendors/{id}` · `GET /work-orders` · `POST /vendors/{vendor}/work-orders` |
| **Assets** | `GET/POST /assets` · `GET/PUT/DELETE /assets/{id}` |
| **Staff** | `GET/POST /staff` · `GET/PUT/DELETE /staff/{id}` |
| **Helpdesk** | `GET/POST /helpdesk` · `GET/PUT/DELETE /helpdesk/{id}` · `POST /helpdesk/{ticket}/reply` · `POST /helpdesk/{ticket}/assign` |
| **Communication** | `GET/POST /communication/broadcasts` · `POST /communication/broadcasts/{broadcast}/send` · `GET/POST /communication/messages` · `GET /communication/messages/{conversation}` |
| **Reports** | `GET /reports/{type}` (`visitor\|billing\|collection\|complaint\|facility\|occupancy\|financial`) |

Each module is additionally **plan-feature-gated** (`feature:<flag>`); calling a module the society's plan doesn't include returns `403`.

## Authorization

Every endpoint enforces the same dynamic RBAC as the web app (policies + `{module}.{ability}` Gates). E.g. a resident can `POST /complaints` and `GET` their own, but cannot assign or resolve; a guard can use `/gate/*` but not approve residents. Super Admins bypass all checks.

## Example

```bash
TOKEN=$(curl -s -X POST localhost:8000/api/v1/auth/login \
  -H 'Accept: application/json' \
  -d email=resident@greenvalley.test -d password=Password@123 -d device_name=cli \
  | jq -r .data.token)

curl -s localhost:8000/api/v1/complaints \
  -H "Authorization: Bearer $TOKEN" -H 'Accept: application/json' | jq

curl -s -X POST localhost:8000/api/v1/complaints \
  -H "Authorization: Bearer $TOKEN" -H 'Accept: application/json' \
  -d title='Lift not working' -d priority=high -d description='Stuck on 3rd floor' | jq
```

> Route names are prefixed `api.v1.*` so they never collide with the identically-named web routes.
