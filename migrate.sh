#!/bin/sh

php artisan migrate:fresh --force
php artisan db:seed --force
