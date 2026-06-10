# CommunityOS — Deployment Guide

Targets a production deployment on **AWS** with MySQL (RDS), Redis (ElastiCache), S3 storage, queue workers and the scheduler. Adapt freely to other hosts.

## 1. Production `.env` essentials

```dotenv
APP_NAME=CommunityOS
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.communityos.io
APP_KEY=base64:…                 # php artisan key:generate

# MySQL (RDS)
DB_CONNECTION=mysql
DB_HOST=your-rds-endpoint.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=communityos
DB_USERNAME=communityos
DB_PASSWORD=********

# Redis (ElastiCache) — cache, sessions, queue
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=your-elasticache-endpoint
REDIS_PORT=6379

# S3 storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=…
AWS_SECRET_ACCESS_KEY=…
AWS_DEFAULT_REGION=ap-south-1
AWS_BUCKET=communityos-prod
AWS_URL=https://communityos-prod.s3.ap-south-1.amazonaws.com

# Mail (SES or SMTP) + SMS/WhatsApp providers
MAIL_MAILER=ses
MAIL_FROM_ADDRESS=no-reply@communityos.io

# Multi-tenancy
TENANCY_RESOLVER=auth            # or `subdomain` for {slug}.communityos.io
APP_CENTRAL_DOMAIN=communityos.io
```

> Multi-tenant storage: `FILESYSTEM_DISK=s3` keeps uploads in S3; module uploads are namespaced per society (`documents/<society_id>/…`, `logos/<society_id>/…`).

## 2. AWS topology (reference)

```
Route 53 ─▶ ALB ─▶ EC2 / ECS (Laravel app, Nginx + PHP-FPM 8.3)  ─▶ RDS MySQL 8 (Multi-AZ)
                              │                                    ─▶ ElastiCache Redis
                              ├─ Queue worker(s)  (supervisor / ECS service)
                              └─ Scheduler (cron / ECS scheduled task)  ─▶ S3 (uploads)  ─▶ SES (mail)
```

- **App tier:** EC2 (Nginx + php-fpm) behind an ALB, or ECS Fargate. Auto-scaling for 1000+ societies.
- **DB:** RDS MySQL 8, Multi-AZ; read replica optional for reporting.
- **Cache/queue/session:** ElastiCache Redis.
- **Storage:** S3 (+ CloudFront for public assets).
- **TLS:** ACM cert on the ALB. For `subdomain` tenancy, use a wildcard cert `*.communityos.io`.

## 3. Provision & release

```bash
git clone … && cd CommunityOS
composer install --no-dev --optimize-autoloader
php artisan key:generate            # first deploy only
php artisan migrate --force         # --seed only on the very first deploy (demo data)
php artisan storage:link
php artisan config:cache route:cache view:cache event:cache
```

Zero-downtime: deploy to a fresh release dir, run `migrate --force`, then atomically swap the symlink and reload php-fpm. Always `php artisan down`/`up` around destructive migrations if needed.

Nginx: point the web root at `public/`, pass `.php` to php-fpm, and set `client_max_body_size` to allow document uploads (e.g. 25M).

## 4. Queue workers

The app pushes notifications and heavy work onto queues (`notifications`, `default`). Run workers under **Supervisor** (EC2) or as a long-running ECS service:

```ini
; /etc/supervisor/conf.d/communityos-worker.conf
[program:communityos-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/communityos/artisan queue:work redis --queue=notifications,default --sleep=1 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/communityos-worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start communityos-worker:*
# after each deploy:
php artisan queue:restart
```

## 5. Scheduler (cron)

A single system cron entry drives Laravel's scheduler (`routes/console.php`):

```cron
* * * * * cd /var/www/communityos && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled tasks already defined:

| Task | Cadence | Purpose |
|------|---------|---------|
| `Jobs\Maintenance\GenerateMonthlyBills` | 1st @ 02:00 | Generate monthly maintenance bills for every society |
| `Jobs\Maintenance\ApplyLateFees` | daily 03:00 | Apply late fees + mark overdue |
| `Jobs\Maintenance\SendDueReminders` | daily 09:00 | Notify residents of upcoming/overdue dues |
| `Jobs\Complaints\EscalateBreachedSlas` | hourly | Escalate SLA-breaching complaints/tickets |
| `communityos:prune-login-history` | daily 01:00 | Retention |
| `sanctum:prune-expired`, `queue:prune-batches`, `auth:clear-resets` | daily / 15-min | Hygiene |

On ECS, run the scheduler as a 1-minute EventBridge scheduled task instead of crond.

## 6. Background services checklist

- [ ] `migrate --force` ran cleanly
- [ ] `config:cache route:cache view:cache event:cache` built
- [ ] ≥2 queue workers running (supervisor/ECS) + `queue:restart` in the deploy hook
- [ ] scheduler cron / EventBridge active
- [ ] Redis reachable (cache/session/queue)
- [ ] S3 bucket + IAM policy; `storage:link` for any local public assets
- [ ] SES (or SMTP) verified sender; SMS/WhatsApp provider creds in Settings → platform payment/notification settings
- [ ] HTTPS enforced; `APP_DEBUG=false`

## 7. Scaling notes (1000+ societies)

- Shared-DB row isolation keeps a single schema — index coverage on `(society_id, …)` keeps tenant queries fast.
- Stateless app tier → scale horizontally behind the ALB.
- Move reporting/analytics reads to an RDS read replica.
- Queue workers scale independently from web; partition by queue if needed.
- Per-tenant cache keys (`tenant:{id}:…`) prevent cross-tenant cache bleed.
- For very large tenants, the architecture supports promoting to `subdomain` resolution and (future) per-tenant DB sharding by swapping the `TenantManager` resolution without touching module code.
