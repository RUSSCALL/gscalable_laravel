#!/bin/bash
set -e

cat > /etc/cron.d/laravel-scheduler << 'EOF'
* * * * * webapp cd /var/app/current && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
EOF

chmod 644 /etc/cron.d/laravel-scheduler
