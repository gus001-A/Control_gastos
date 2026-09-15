#!/bin/sh
set -e

# Cachea configuración/rutas para arrancar más rápido
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Aplica migraciones pendientes contra la base de datos configurada (Supabase/Postgres)
php artisan migrate --force

# Render inyecta el puerto real en $PORT
PORT="${PORT:-8080}"
exec php artisan serve --host=0.0.0.0 --port="$PORT"
