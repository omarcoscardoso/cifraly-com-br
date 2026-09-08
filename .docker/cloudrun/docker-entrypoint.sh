#!/bin/sh
set -e

# Default Cloud Run port to 8080 if not set
export PORT=${PORT:-8080}

# Replace $PORT placeholder in Nginx config template
envsubst '$PORT' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf

# Rebuild Laravel caches if APP_KEY is present
if [ -n "$APP_KEY" ]; then
    echo "Caching Laravel configuration, routes and views..."
    php /var/www/html/artisan config:cache || true
    php /var/www/html/artisan route:cache || true
    php /var/www/html/artisan view:cache || true
fi

# Ensure correct permissions for storage and bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Start PHP-FPM in background
echo "Starting PHP-FPM..."
php-fpm -D

# Start Nginx in foreground to keep container running
echo "Starting Nginx on port ${PORT}..."
exec nginx -g "daemon off;"
