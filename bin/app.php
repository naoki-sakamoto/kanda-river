<?php
require_once("../data/config/" . "conf.php");
require_once("config/" . "define.php");
class app {
  private $obj;
  private $init_status = false;
  private $_session_key;//セッションキー

  function __construct() {
    global $program_mode;//プログラムモード

    if ($this->init_status == true) return;//二回目以降は通らない。
    $this->obj = null;

    ini_set('include_path', PEARDIR . ':' . ini_get('include_path'));
    date_default_timezone_set('Asia/Tokyo');

      include_once(LIBPATH . "mods/appController.php");
    if ($program_mode == WEB_MODE) {
      include_once(PEARDIR . "/HTML/Template/Sigma.php");
      include_once(LIBPATH . "mods/tau.php");
      include_once(LIBPATH . "mods/modSession.php");
      include_once(LIBPATH . "mods/mobile.php");
    }
    include_once(LIBPATH . "mods/putlog.php");
    include_once(LIBPATH . "mods/db.php");
    include_once(LIBPATH . "mods/common.php");

    $this->init_status = true;//一度初期化したら二度と初期化しないようにする。
  }

  public function create_object() {
    global $program_mode;
    if ($this->obj != null) return $this->obj;

    if ($program_mode == WEB_MODE) {
      $this->obj["param"]["GET"] = $_GET;
      $this->obj["param"]["POST"] = $_POST;

    }

    $db_server["master"] = MASTER_DB_SERVER;
    $db_name["master"] = MASTER_DB_NAME;
    $db_user["master"] = MASTER_DB_USER;
    $db_password["master"] = MASTER_DB_PASSWORD;

    $db_server["slave"] = SLAVE_DB_SERVER;
    $db_name["slave"] = SLAVE_DB_NAME;
    $db_user["slave"] = SLAVE_DB_USER;
    $db_password["slave"] = SLAVE_DB_PASSWORD;

    $db_server["mdb"] = MDB_DB_SERVER;
    $db_name["mdb"] = MDB_DB_NAME;
    $db_user["mdb"] = MDB_DB_USER;
    $db_password["mdb"] = MDB_DB_PASSWORD;

    //データベースへ接続
    $this->obj["db_master"] = $this->create_db_object($db_server["master"], $db_name["master"], $db_user["master"], $db_password["master"]);
    //$this->obj["db_mdb"] = $this->create_db_object($db_server["mdb"], $db_name["mdb"], $db_user["mdb"], $db_password["mdb"]);

    //ログ
    //$putlog = new putlog();
    $putlog = putlog::singleton();
    if (ENV < 2) $putlog->set_debug(true);
    $this->obj["putlog"] = &$putlog;

    //共通モジュール
    $common = new common();
    $common->putlog = &$putlog;
    $this->obj["common"] = &$common;


    if ($program_mode == WEB_MODE) {
      if ($this->obj["common"]->isEmpty($_GET["api"]) == false) {
        $this->obj["param"]["ARGS"] = $_GET;
      } else {
        $this->obj["param"]["ARGS"] = $_POST;
      }

      //if (ENV < 2) {
        $this->obj["putlog"]->d("idx","======START======");
        $this->obj["putlog"]->d("idx",$this->obj["common"]->get_date($this->obj["db_master"]));
        $client_ip_address = $this->obj["common"]->get_client_ip_address();
        $this->obj["putlog"]->d("idx","ip:".$client_ip_address["ip"]);
        $this->obj["putlog"]->d("idx","host:".$client_ip_address["host"]);
        d("idx","param.GET=>".print_r($this->obj["param"]["GET"],true));
        d("idx","param.POST=>".print_r($this->obj["param"]["POST"],true));
        d("idx","param.ARGS=>".print_r($this->obj["param"]["ARGS"],true));
      //}

      //$this->obj["mem"] = memcache_control::singleton();
      $mbl = new mobile();
      $mbl->setUserAgent($_SERVER['HTTP_USER_AGENT']);
      $this->obj["mobile"] = &$mbl;

      //---------------------------------------------
      // セッション管理の設定
      //---------------------------------------------
      $ss = new modSession();
      //$ss->SessionStart($mbl->getCareer(),$mbl->getSolidNumber());
      $ss->SessionStart("",$mbl->getSolidNumber());
      $id = $ss->get_session_id();
      d("idx","session_id=>".$id);
      $this->obj["ss"] = &$ss;
      /*
      $kvs = new object_store($id, $this->obj["db_master"], $this->obj["db_slave"], SESSION_LIFE_TIME);
      $_SESSION[$ss->prefix] = $kvs->get();
      $this->obj["kvs"] = &$kvs;
      */

      $this->obj["mods"] = array();
    }
    return $this->obj;
  }

  private function create_db_object($db_server, $db_name, $db_user, $db_password) {
    $db = new db();
    if (ENV < 2) $db->set_debug(true);

    $db->connect($db_server, $db_name, $db_user, $db_password);
    $db->set_charset("utf8");
    return clone $db;
  }

  public function execution(){
    global $directory_access;
    global $url;
    global $program_mode;

    $obj = $this->create_object();

    if ($program_mode == WEB_MODE) {
      $url = $_GET["url"];//パスを取得
      $url = $this->delete_extention($url);

      if ($url["filename"] == "") $url["filename"] = "index";
      $controller = "controller".$directory_access.$url["filename"].".php";//.$url["extention"];
      $this->obj["putlog"]->d("idx","controller:".$controller);
    } else if ($program_mode == BATCH_MODE) {
      $url = $_SERVER["PHP_SELF"];//パスを取得
      $url = $this->delete_extention($url);
      $controller = "batch/".$url["filename"].".php";
    }

    if ($program_mode == WEB_MODE) {
      if (file_exists($controller)) {
        require_once($controller);
        $controller = new controller();
        $controller->career = $obj["mobile"]->getCareer();
        if ($controller->career == "") $controller->career = "pc";
        $controller->solid_number = $obj["mobile"]->getSolidNumber();
        $controller->common = &$obj["common"];
        $controller->mem = &$obj["mem"];
        $controller->ss = &$obj["ss"];
        $controller->putlog = &$obj["putlog"];
        $controller->db_master = &$obj["db_master"];
        $controller->db_slave = &$obj["db_master"];
        //$controller->db_mdb = &$obj["db_mdb"];
        $controller->param = &$obj["param"];
        $controller->session_key = $this->session_key($controller->career,$controller->solid_number);
        $controller->mods = &$obj["mods"];

        //$controller->mem->session_key = $controller->session_key;
        $controller->begin_start();
        $ret = $controller->logic();
        $controller->begin_end();
      } else {
        echo "404";
      }
    } else if ($program_mode == BATCH_MODE) {
      $controller = new controller();
      $controller->common = &$obj["common"];
      $controller->putlog = &$obj["putlog"];
      $controller->db_master = &$obj["db_master"];
      $controller->db_slave = &$obj["db_master"];
      //$controller->db_mdb = &$obj["db_mdb"];
      $controller->mods = &$obj["mods"];
      $controller->set_db($obj["db_master"], $obj["db_master"]);

      $ret = $controller->logic();
    }

  }

/**
 *  ファイル名と拡張子を分けて返す
 *
 * @since     1.0
 * @access    private
 * @param     string          ファイル名
 * @return    array filename  ファイル名(拡張子なし)
 *                  extention 拡張子
 */
  private function delete_extention($str) {
    $work = explode(".", $str);
    $ext = $work[count($work)-1];

    $ret["filename"] = basename($str, '.' . $ext);
    $ret["extention"] = $ext;

    return $ret;
  }

/**
 *  セッションIDの取得
 *
 * @since     1.0
 * @access    private
 * @param     void
 * @return    string セッションID
 */
  private function session_key($career,$solid_number) {
    $session_key = "";
    //PCとスマホ
    if ($career == "pc" || $career == "Android" || $career == "iPhone" || $career == "iPad") {
      $this->_session_key = @$_COOKIE['session_key'];
      if ( !$this->_session_key ) {
        //セッションキーの生成
        $this->_session_key = md5($_SERVER['SERVER_ADDR']."$".uniqid(rand(), true));
      }
      setcookie( 'session_key', $this->_session_key, time() + SESSION_LIFETIME, '/' );
    } else {
      if ($solid_number == "") {
        $this->_session_key = md5($_SERVER['SERVER_ADDR']."$".uniqid(rand(), true));
      } else {
        $this->_session_key = $solid_number;
      }
    }
    return $this->_session_key;

    /*
    $this->_session_key = @$_COOKIE['session_key'];
    if ( !$this->_session_key ) {
      //セッションキーの生成
      $this->_session_key = md5($_SERVER['SERVER_ADDR']."$".uniqid(rand(), true));
    }
    setcookie( 'session_key', $this->_session_key, time() + SESSION_LIFETIME, '/' );
    return $this->_session_key;
    */
  }


}
$c = new app();
$c->execution();
?>