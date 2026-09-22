#!/bin/bash

cd /var/app/current

php artisan config:clear
php artisan migrate --force

# Idempotent: creates the role rows only if missing.
php artisan db:seed --class=RoleSeeder --force
