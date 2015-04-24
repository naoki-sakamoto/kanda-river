#! /usr/local/bin/php
<?php
/* -------------------------------------------------------------
 * システム     ：バッチシステム
 * 種別         ：共通
 * プログラム名 ：batch_template.php
 * 概要         ：バッチプログラムのテンプレート
 *
 * 作成日       ：2013/08/25
 * 作成者       ：n.sakamoto
 * 備考         ：
 *
 * [更新履歴]
 * -------------+---------------+-------------------------------
 * 日  付       | 名  前        | 概  要
 * -------------+---------------+-------------------------------
 * 2013/08/25   | n.sakamoto    | 新規作成
 * -------------------------------------------------------------
 */
/*
php /home/yell/app/bin/batch/batch_template.php 2 > /dev/null
php /cygdrive/c/pleiades/workspace/yell/app/bin/batch/batch_template.php 0
*/
ini_set( 'mbstring.internal_encoding', "UTF-8" );

// 引数のチェック
if( !(isset ($_SERVER["argv"]["1"])) ) {
 print "【ERROR】引数を正しく設定してください。\n";
 exit;
}

$env = $_SERVER["argv"]["1"];// 0:ローカル環境 1:テスト環境 2:本番環境
$LIBPATH = "";
if ($env == 0) {
  $LIBPATH = "C:/pleiades/workspace/yell/app/bin/";
} else {
  $LIBPATH = "/home/yell/app/bin";
}
chdir( $LIBPATH );
echo getcwd()."\n";
global $program_mode;
$program_mode = "batch";
include_once($LIBPATH . "mods/util.php");

class controller extends util {
  public function logic() {
    d("batch program","batch_template Start!!");

    d("batch program","batch_template End!!");
  }

}
include_once("app.php");
?>