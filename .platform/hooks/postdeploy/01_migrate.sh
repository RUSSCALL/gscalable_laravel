#!/bin/bash

cd /var/app/current

php artisan config:clear
php artisan migrate --force

