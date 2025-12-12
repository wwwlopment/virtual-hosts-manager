#!/bin/bash
set -e

echo "Starting Laravel application setup..."

cd /var/www/html

# Check if .env exists, if not create from example
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
fi

# Install composer dependencies if vendor doesn't exist
if [ ! -d vendor ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader
fi

# Generate APP_KEY if it doesn't exist
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Create storage link if it doesn't exist
if [ ! -L public/storage ]; then
    echo "Creating storage link..."
    php artisan storage:link
fi

echo "Setup complete! Starting PHP-FPM..."

# Start PHP-FPM
exec php-fpm
