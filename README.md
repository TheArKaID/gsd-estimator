# GSD Estimator

A web-based GSD Software Project Sstimation tool built with Laravel.

## Deployment Guide

This guide covers two methods of deploying the GSD Estimator application:
1. Manual deployment with Nginx
2. Docker deployment

### Prerequisites

- PHP 8.4 or higher
- Composer
- MySQL 5.7 or higher
- Git
- Node.js and NPM (for frontend assets)

## Method 1: Manual Deployment with Nginx

### Step 1: Server Setup

Ensure your server has the following PHP extensions installed:
```bash
sudo apt update
# Either install PHP 8.3 or PHP 8.4
sudo apt install php8.4-fpm php8.4-mysql php8.4-mbstring php8.4-xml php8.4-bcmath php8.4-gd php8.4-curl php8.4-zip
```

### Step 2: Clone the Repository

```bash
git clone https://github.com/TheArKaID/gsd-estimator.git
cd gsd-estimator
```

### Step 3: Install Dependencies

```bash
composer install
npm install
npm run build
```

### Step 4: Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` file with your database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gsd
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 5: Set Permissions

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Step 6: Configure Nginx

Create a new Nginx site configuration:

```bash
sudo nano /etc/nginx/sites-available/gsd-estimator
```

Add the following configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/gsd-estimator/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site and restart Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/gsd-estimator /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 7: Run Migrations and Seeders

```bash
php artisan migrate
php artisan db:seed
```

## Method 2: Docker Deployment

### Option A: Using Docker Run

1. Pull the image:
   ```bash
   docker pull thearka/gsd-estimator:latest
   ```

2. Create a network:
   ```bash
   docker network create gsd-network
   ```

3. Run MySQL container:
   ```bash
   docker run -d \
     --name gsd-mysql \
     --network gsd-network \
     -e MYSQL_ROOT_PASSWORD=Adminjuga \
     -e MYSQL_DATABASE=gsd \
     -e MYSQL_USER=gsd \
     -e MYSQL_PASSWORD=Bukanadmin123 \
     -v mysql-data:/var/lib/mysql \
     -p 3336:3306 \
     mysql:8.0
   ```

4. Run the application container:
   ```bash
   docker run -d \
     --name gsd-app \
     --network gsd-network \
     -p 8881:8000 \
     -e DB_CONNECTION=mysql \
     -e DB_HOST=gsd-mysql \
     -e DB_PORT=3306 \
     -e DB_DATABASE=gsd \
     -e DB_USERNAME=gsd \
     -e DB_PASSWORD=Bukanadmin123 \
     -v app-data:/app/storage/app \
     thearka/gsd-estimator:latest
   ```

### Option B: Using Docker Compose

1. Clone the repository:
   ```bash
   git clone https://github.com/TheArKaID/gsd-estimator.git
   cd gsd-estimator
   ```

2. Start the services:
   ```bash
   docker-compose up -d
   ```

Docker Compose will automatically:
- Pull the latest images (or build the application image if it doesn't exist)
- Start the MySQL database
- Start the application container
- Set up volumes for data persistence
- Connect the containers with a network

The application will be accessible at http://localhost:8881

## Migrations and Seeders

### Running Migrations Manually

```bash
# For local setup
php artisan migrate

# For production
php artisan migrate --force
```

### Running Seeders Manually

```bash
# For local setup
php artisan db:seed

# For production
php artisan db:seed --force
```

### Docker Environment

In the Docker setup, migrations and seeders are automatically run during container startup via the entrypoint script. This ensures the database is properly initialized.

The entrypoint script:
1. Waits for the MySQL database to be available
2. Runs the migrations
3. Seeds the database (only if it hasn't been seeded before)

To manually run migrations in Docker:

```bash
docker exec -it gsd-app php artisan migrate
```

To manually run seeders in Docker:

```bash
docker exec -it gsd-app php artisan db:seed
```

## Troubleshooting

### Common Issues

1. **Connection to Database Failed**: Verify your database credentials in the `.env` file.

2. **Permission Issues**: Ensure proper permissions for storage and bootstrap/cache directories.

3. **Docker Container Fails to Start**: Check logs with `docker logs gsd-app`.

### Getting Logs

```bash
# Application logs (Manual install)
tail -f storage/logs/laravel.log

# Docker container logs
docker logs -f gsd-app
```