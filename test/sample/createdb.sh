#!/bin/bash

MYSQL=/Applications/MAMP/Library/bin/mysql
SOCKFILE=/Applications/MAMP/tmp/mysql/mysql.sock
ROOT_PASSWORD=root
CREATEDB_SQL=$(dirname $0)/createdb.sql

${MYSQL} -u root -p${ROOT_PASSWORD} --socket=${SOCKFILE} < ${CREATEDB_SQL}
