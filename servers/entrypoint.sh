#!/bin/sh
set -e

echo "Waiting for MySQL to be available..."
# Replace 'mysql' with the hostname of your MySQL container if different
while ! nc -z mysql 3306; do
  sleep 1
done
echo "MySQL is up - proceeding with migrations."

# Run migrations and seed the database if needed
php artisan migrate --force

# Check for a marker file indicating that seeds have run
if [ ! -f /app/storage/app/seeds_run ]; then
    echo "Seeding database..."
    php artisan db:seed --force
    touch storage/app/seeds_run
fi

# Install FrankenPHP binaries if not already installed
# php artisan octane:frankenphp&

# Finally, start Supervisor (or your desired process manager)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf