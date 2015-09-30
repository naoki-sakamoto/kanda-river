<?php
ini_set('display_errors', 'On');

define("BASEPATH", $_SERVER['HOME']."/works/kanda-river/");

define("LIBPATH", BASEPATH."bin/");
define("DATAPATH", BASEPATH."data/");
define("ROOTPATH", BASEPATH."html");
define('TOP_DOMAIN', '127.0.0.1:8081');
define('TOP_URL', 'http://127.0.0.1:8081/');//トップページのURL

// MASTER_DB
define('MASTER_DB_SERVER', '127.0.0.1');
define('MASTER_DB_NAME', 'sampledb');
define('MASTER_DB_USER', 'sample');
define('MASTER_DB_PASSWORD', 'password');

// MAMPでMySQLを動作させる場合必須
define('MASTER_DB_UNIX_SOCKET', '/Applications/MAMP/tmp/mysql/mysql.sock');

define('HASH_SALT', 'vKBU49a95NR');
define("TWITTER_OAUTH_CALLBACK_URL", "http://www.ttm.ms/admin/");

define("VIEWPATH", LIBPATH."view/");
