<?php
/*
 * php.ini
 * date.timezone = Asia/Tokyo
 */
define("WEB_MODE", "web");// WEB
define("BATCH_MODE", "batch");// バッチ処理
global $program_mode;//プログラムモード
if ($program_mode == null) $program_mode = WEB_MODE;//デフォルト設定

if (ENV == 0) {
  define("LIBPATH", "C:/pleiades/workspace/kanda-river/bin/");
  define("DATAPATH", "C:/pleiades/workspace/kanda-river/data/");
}
define("LOGPATH", DATAPATH."logs/");
define('PEARDIR', LIBPATH . 'PEAR/');// PEARディレクトリ

ini_set('error_reporting', E_ALL & ~E_NOTICE);
//ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_DEPRECATED);
//error_reporting(E_ALL & ~E_DEPRECATED);
if (ENV <= 2) {
  ini_set('display_errors', On);
}

/*
 * ログ関連
 */
ini_set("log_errors", 1);
$day = date("Ymd");
define("DEBUG_LOG_PATH", LOGPATH."debug".$day.".log");
ini_set('error_log', DEBUG_LOG_PATH);
define("SQL_LOG", 0);//1:SQL文を出力する
define("SOURCE_VERSION", "20140910_1");//ソースコードのバージョン

/*
 * プロジェクト名
 */
define('PROJECT_NAME', 'kanda-river');// プロジェクト名

/*
 * セッション関連
 */
define( 'SESSION_LIFETIME', 60 * 60 * 24 * 1 );

if (ENV == 0) {
 define("ROOTPATH", "C:/pleiades/workspace/injury-db/html");//ルートパス
 define('TOP_DOMAIN', '127.0.0.1:8081');
 define('TOP_URL', 'http://127.0.0.1:8081/injury-db/html/');//トップページのURL
  //MASTER_DB
 define('MASTER_DB_SERVER', 'localhost');
 define('MASTER_DB_NAME', 'test');
 define('MASTER_DB_USER', '');
 define('MASTER_DB_PASSWORD', '');


 define('HASH_SALT', 'vKBU49a95NR');
 define("TWITTER_OAUTH_CALLBACK_URL", "http://www.ttm.ms/admin/");

  define("VIEWPATH", LIBPATH."view/");
}

/*
 * ログインチェックをしないページ
*/
global $noLoginCheck;
$noLoginCheck = array(
    "index",
    "DB.sample",
);
?>