<?php
/*
 * php.ini
 * date.timezone = Asia/Tokyo
 */

/*
 * 環境毎設定ファイル読み込み
 */
if (ENV == 0) {
  require_once("development.php");  // ローカル環境
} else if (ENV == 1) {
  require_once("test.php");  // テスト環境
} else if (ENV == 2) {
  require_once("production.php");  // 本番環境
}

define("WEB_MODE", "web");// WEB
define("BATCH_MODE", "batch");// バッチ処理
global $program_mode;//プログラムモード
if ($program_mode == null) $program_mode = WEB_MODE;//デフォルト設定

define("LOGPATH", DATAPATH."logs/");
define('PEARDIR', LIBPATH . 'PEAR/');// PEARディレクトリ

ini_set('error_reporting', E_ALL & ~E_NOTICE);
//ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_DEPRECATED);
//error_reporting(E_ALL & ~E_DEPRECATED);

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
define('PROJECT_NAME', 'git-kandagawa');// プロジェクト名

/*
 * セッション関連
 */
define( 'SESSION_LIFETIME', 60 * 60 * 24 * 1 );

/*
 * ログインチェックをしないページ
*/
global $noLoginCheck;
$noLoginCheck = array(
    "index",
    "DB.sample",
    "jsonTest"
);
?>