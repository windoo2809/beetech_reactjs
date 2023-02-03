#!/bin/bash

cd /app
composer install
cd customer_api 
composer install

ENVFILE=".env"
TEMP=".env.dev"
if [ -f "$ENVFILE" ]; then
    echo "$ENVFILE exists."
else 
    echo "$ENVFILE does not exist."
    cp $TEMP $ENVFILE
    php artisan key:generate -v
fi

echo "install commited."
