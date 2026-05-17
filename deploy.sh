#!/bin/bash
set -e

APP_DIR="/var/www/cash-flow-system"

echo "🚀 Starting deployment..."

cd $APP_DIR

# Reset any local changes
git reset --hard
git pull origin main

# Install PHP dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader

# Run migrations (safe, will only apply new ones)
php artisan migrate --force

# Install JS deps + build
npm install
npm run build

# Fix permissions
sudo chown -R mmanuel:apache $APP_DIR
sudo chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache

# Restart apache
sudo systemctl restart httpd

echo "✅ Deployment complete!"
