#!/bin/sh
set -e

# Wait for MySQL
while ! nc -z "$DB_HOST" "$DB_PORT"; do
  echo "Waiting for database at $DB_HOST:$DB_PORT..."
  sleep 1
done

# Wait for Redis
while ! nc -z "$REDIS_HOST" "$REDIS_PORT"; do
  echo "Waiting for Redis at $REDIS_HOST:$REDIS_PORT..."
  sleep 1
done

# Cache configs
php artisan config:cache
php artisan route:cache
php artisan view:cache || true

# Run migrations automatically only in non-production environments
if [ "$APP_ENV" != "production" ]; then
  echo "Running migrations (APP_ENV=$APP_ENV)..."
  php artisan migrate --force
else
  echo "Skipping migrations (APP_ENV=production). Run via CI/CD pipeline."
fi

if [ $# -gt 0 ]; then
  exec "$@"
fi

# Start PHP-FPM in foreground
exec php-fpm -F
