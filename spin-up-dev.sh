#!/bin/bash

docker run -d \
	--name pg_db \
    -e POSTGRES_USER=pg_user \
	-e POSTGRES_PASSWORD=pg_pass \
	postgres