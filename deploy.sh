#!/bin/sh

php artisan down
git pull origin develop
composer install --no-interaction --optimize-autoloader --no-dev
php artisan migrate --force
php artisan optimize:clear
php artisan view:clear
php artisan queue:restart
php artisan up
