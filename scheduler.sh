#!/bin/bash
cd /var/www/vhosts/adcompro.app/progress.adcompro.app
/usr/bin/php artisan schedule:run >> /dev/null 2>&1
