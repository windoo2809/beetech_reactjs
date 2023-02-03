#!/bin/bash

sh /install.sh
/usr/sbin/php-fpm 
/usr/bin/supervisord
/usr/bin/supervisorctl reread
/usr/bin/supervisorctl update
/usr/bin/supervisorctl start laravel-worker:*
/usr/sbin/httpd -DFOREGROUND

while true
do
    sleep 10
done
 
