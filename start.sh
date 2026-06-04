#!/bin/bash
mkdir -p /app/database
touch /app/database/database.sqlite
chmod 777 /app/database/database.sqlite
php artisan config:clear
php artisan migrate --force
php artisan serve --host=0.0.0.0 --port=$PORT