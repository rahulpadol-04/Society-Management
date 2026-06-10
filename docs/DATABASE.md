# CommunityOS — Database Schema & ER

**81 tables.** Tenant-scoped tables carry a `society_id` FK (cascade on delete) + composite indexes on `(society_id, status/type/…)`. Within-module relations use real FK constraints; cross-module references are **soft links** (`unsignedBigInteger` + index, no constraint). Most primary entities carry `timestamps` + `softDeletes`.

## Migration order (FK-safe)

```
0000_…_subscription_plans, societies, subscriptions (+ subscription_invoices)
0001_…_users (+ password_reset_tokens, sessions), cache, jobs
2026_…_personal_access_tokens
2026_…_roles, permissions, rbac_pivots (permission_role, role_user, permission_user)
2026_…_settings, audit_and_security (audit_logs, login_histories, password_histories), notifications
2026_06_04_000001 complaints     · 000002 structure   · 000003 residents
            000004 visitors       · 000005 maintenance · 000006 facilities
            000007 notices        · 000008 accounting  · 000009 assets
            000010 vendors        · 000011 staff       · 000012 helpdesk
            000013 communication  · 000014 platform (cms_pages, blogs, contact_inquiries, payment_gateways)
```

## Central (non-tenant) tables
`societies` · `subscription_plans` · `subscriptions` · `subscription_invoices` · `users` · `roles` · `permissions` · `permission_role` · `role_user` · `permission_user` · `cms_pages` · `blogs` · `contact_inquiries` · `payment_gateways` · framework tables (`cache`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `migrations`, `personal_access_tokens`, `notifications`).

> `users.society_id` is nullable (NULL = platform user / Super Admin). `roles.society_id` nullable (NULL = global role).

## ER by domain (key columns & relations)

### Platform / tenancy
- **societies** `id, name, slug*, registration_number, email, phone, address…, timezone, currency, current_plan_id→subscription_plans, subscription_status, trial_ends_at, subscription_ends_at, status, settings(json)`
- **subscription_plans** `id, name, slug*, billing_cycle, price, trial_days, max_units, max_users, max_storage_mb, features(json), is_active, is_featured`
- **subscriptions** `id, society_id→societies, subscription_plan_id, status, amount, billing_cycle, starts_at, ends_at, gateway, …`
- **subscription_invoices** `id, society_id, subscription_id, invoice_number*, amount, tax, total, status(paid/…), paid_at`

### Identity & RBAC
- **users** `id, society_id?→societies, name, email*, phone, password, 2FA cols, last_login_*, …`
- **roles** `id, society_id?, name, slug, scope(global/society), level, is_system` · unique `(society_id, slug)`
- **permissions** `id, name, slug*, module, ability, group`
- pivots **role_user**, **permission_role**, **permission_user**

### Security / audit
- **audit_logs** `morph auditable, society_id?, user_id?, event, old/new(json), ip, ua, url`
- **login_histories** `user_id?, status, ip, device, platform, browser, logged_in_at`
- **password_histories** `user_id, password` · **settings** `society_id?, group, key, value, type` unique `(society_id, key)`

### Structure (000002)
- **towers** `society_id, name, code, type, total_floors, units_per_floor, status`
- **floors** `society_id, tower_id→towers, name, number`
- **flats** `society_id, tower_id→towers, floor_id?→floors, number, type, carpet_area, ownership, status, owner_id⋯users, maintenance_amount`
- **parking_slots** `society_id, code, type, flat_id?→flats, vehicle_id⋯, status`
- **society_documents** `society_id, title, category, file_path, is_public, uploaded_by⋯`

### People (000003)
- **residents** `society_id, user_id⋯, flat_id⋯, parent_id⋯ (family), type(owner/tenant/family_member), name, relation, is_primary, status`
- **emergency_contacts** `society_id, resident_id→residents, name, phone, relation`
- **vehicles** `society_id, flat_id⋯, resident_id⋯, parking_slot_id⋯, type, registration_number, …` unique `(society_id, registration_number)`

### Visitors (000004)
- **visitor_passes** `society_id, code*, flat_id⋯, host_id⋯users, type, status, valid_until, max_entries, entries_used, approved_by⋯` (softDeletes)
- **visitor_logs** `society_id, visitor_pass_id?→visitor_passes, flat_id⋯, type, checked_in_at, checked_out_at, status, guard_id⋯`

### Complaints (000001)
- **complaint_categories** `society_id, name, sla_hours`
- **complaints** `society_id, reference*, complaint_category_id?, raised_by→users, assigned_to?→users, flat_id⋯, priority, status, sla_due_at, resolved_at`
- **complaint_activities** (timeline) · **complaint_feedback** `complaint_id*, rating`

### Maintenance billing (000005)
- **maintenance_heads** `society_id, name, type(fixed/per_sqft/…), amount, is_taxable, gst_percentage, frequency`
- **maintenance_bills** `society_id, bill_number*, flat_id⋯, user_id⋯, period, due_date, subtotal, tax_amount, late_fee, total, paid_amount, status, line_items(json)`
- **maintenance_payments** `society_id, receipt_number*, maintenance_bill_id→bills, amount, method, paid_at`
- **late_fees** · **invoice_templates**

### Facilities (000006)
- **facilities** `society_id, name, type, capacity, charge, requires_approval, slot_minutes, is_active`
- **facility_bookings** `society_id, facility_id→facilities, user_id⋯, flat_id⋯, booking_date, start_time, end_time, amount, status, approved_by⋯`

### Notices (000007)
- **notices** `society_id, title, body, category, author_id⋯, audience, is_published, pinned, event_at`
- **polls** `society_id, notice_id?, question, multiple_choice, closes_at` · **poll_options** `votes_count` · **poll_votes** unique `(poll_option_id, user_id)`

### Accounting (000008)
- **ledger_accounts** `society_id, code, name, type(asset/liability/equity/income/expense), opening_balance` unique `(society_id, code)`
- **journal_entries** `society_id, reference*, entry_date, type, status(draft/posted), amount, posted_by⋯`
- **journal_lines** `society_id, journal_entry_id→entries, ledger_account_id→accounts, debit, credit`
- **bank_accounts** `society_id, ledger_account_id⋯, account_type(bank/cash), current_balance`

### Assets (000009)
- **asset_categories** `depreciation_rate, useful_life_years`
- **assets** `society_id, asset_category_id⋯, tower_id⋯, vendor_id⋯, purchase_cost, salvage_value, depreciation_method, current_value, status`
- **asset_maintenance_schedules** (`frequency, next_due_date, status`) · **asset_maintenance_logs**

### Vendors (000010)
- **vendors** `society_id, name, category, rating, ratings_count, status`
- **vendor_contracts** · **work_orders** `reference*, vendor_id⋯, complaint_id⋯, status, amount` · **vendor_payments** · **vendor_ratings**

### Staff (000011)
- **staff_members** `society_id, user_id⋯, employee_code, department, salary, shift, status`
- **staff_attendances** unique `(staff_member_id, date)` · **staff_shifts** · **staff_leaves** · **payrolls** unique `(staff_member_id, period)`

### Helpdesk (000012)
- **support_tickets** `society_id, ticket_number*, category, priority, status, raised_by⋯, assigned_to⋯, sla_due_at, escalation_level`
- **ticket_replies** (`is_internal`) · **ticket_activities** · **escalation_rules** `level, after_hours`

### Communication (000013)
- **message_templates** `channel, subject, body, variables(json)`
- **broadcasts** `title, message, channels(json), audience, status, recipients_count` · **broadcast_recipients**
- **conversations** · **conversation_participants** unique `(conversation_id, user_id)` · **messages**

### Platform CMS (000014)
- **cms_pages** `title, slug*, content, status, published_at`
- **blogs** `title, slug*, excerpt, content, author_id⋯, status, views`
- **contact_inquiries** `name, email, subject, message, status`
- **payment_gateways** `name, provider, mode, is_active, credentials(json)`

`*` = unique · `→` = FK constraint · `⋯` = soft link (no constraint) · `?` = nullable

## Indexing strategy
- Every tenant table: `(society_id, <status|type|date>)` composite indexes for the common scoped+filtered list queries.
- Unique business keys are scoped to the tenant where appropriate (`flats.number`, `vehicles.registration_number`, `parking_slots.code`, `ledger_accounts.code` use `(society_id, …)`), while globally-unique references (`bill_number`, `reference`, `ticket_number`, `code`) are plain unique.
- Soft-link columns (`flat_id`, `user_id`, `vendor_id`, …) are individually indexed for joins/filters.
