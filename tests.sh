#!/bin/bash
echo "Starting tests" >&1
php --version \
	&& composer --version \
 	&& /code/vendor/bin/phpcs --standard=psr2 -n --ignore=vendor --extensions=php . \

mkdir /data/
mkdir /data/out/
mkdir /data/out/tables/
file="/data/out/tables/dataset.csv"
echo $KBC_CONFIG_FILE > /data/config.json

php /code/main.php

if [ -f "$file" ]
then
	echo "$file found." >&1
	exit 0
else
	echo "$file not found." >&2
	exit 1
fi
