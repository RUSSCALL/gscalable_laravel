#!/bin/bash

cd /var/app/current

php artisan config:clear
php artisan db:wipe --force
php artisan migrate --force