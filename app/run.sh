#!/bin/bash

SCRIPT_DIR=$(cd $(dirname $0); pwd)
cd $SCRIPT_DIR

COMPOSEFILE="../docker-compose.yml"
TEMP="docker-compose-template.yml"
if [ -f "$COMPOSEFILE" ]; then
    echo "$COMPOSEFILE exists."
else 
    echo "$COMPOSEFILE does not exist."
    cp $TEMP $COMPOSEFILE
fi

cd ..

export COMPOSE_HTTP_TIMEOUT=300

docker-compose build
docker-compose down
docker-compose up -d
