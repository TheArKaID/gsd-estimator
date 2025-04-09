FROM dunglas/frankenphp

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git zip unzip netcat-openbsd supervisor \
    libzip-dev

RUN install-php-extensions \
    gd pcntl opcache pdo pdo_mysql zip

# Verify ZIP extension is installed
RUN php -m | grep -i zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy the Laravel application files into the container.
COPY . .

# Copy Supervisor configuration and entrypoint script
COPY servers/*.conf /etc/supervisor/conf.d/
COPY servers/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Install Laravel dependencies using Composer.
RUN composer install

# Set permissions for Laravel.
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
