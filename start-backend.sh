#!/usr/bin/env bash
# Brings up CommunityOS backend: MySQL (port 33061, AppArmor needs /tmp) + Laravel API (port 8000).
set -e
DATADIR=/tmp/mysql-communityos
SOCK=/tmp/mysql-co.sock
if ! mysqladmin --no-defaults -uroot --socket="$SOCK" ping >/dev/null 2>&1; then
  if [ ! -d "$DATADIR/mysql" ]; then
    echo "Initializing MySQL data dir..."; mkdir -p "$DATADIR"
    mysqld --no-defaults --initialize-insecure --datadir="$DATADIR"
  fi
  echo "Starting MySQL on :33061..."
  mysqld --no-defaults --datadir="$DATADIR" --socket="$SOCK" --port=33061 --pid-file=/tmp/mysql-co.pid &
  for i in $(seq 1 20); do mysqladmin --no-defaults -uroot --socket="$SOCK" ping >/dev/null 2>&1 && break; sleep 1; done
  mysql --no-defaults -uroot --socket="$SOCK" -e "CREATE DATABASE IF NOT EXISTS communityos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
  # seed only if empty
  cd /home/crematrix/CommunityOS
  if ! DB_HOST=127.0.0.1 DB_PORT=33061 DB_DATABASE=communityos php artisan tinker --execute='exit(\Schema::hasTable("users")?0:1);' >/dev/null 2>&1; then
    DB_HOST=127.0.0.1 DB_PORT=33061 DB_DATABASE=communityos php artisan migrate:fresh --seed
  fi
fi
echo "Starting Laravel API on http://localhost:8000 ..."
cd /home/crematrix/CommunityOS
DB_HOST=127.0.0.1 DB_PORT=33061 DB_DATABASE=communityos exec php artisan serve --host=127.0.0.1 --port=8000
