<?php
/**
 * ベースモジュール
 *
 * @version   $Id: base_module.php,v 2.1
 * @access    public
 */
class appController {
  public $mods;
  public $dispdt;
  public $param;
  public $common;
  public $mem;
  public $putlog;
  public $db_master;
  public $session_key;
  public $career;
  public $solid_number;
  public $client_category;
  private $render_status = false;
  /**
   * 言語(ja:日本語 en:英語)
   */
  public $language;//言語(ja:日本語 en:英語)

  /**
   *セキュリティレベル
   *0:コーチ
   *1:選手
   *
   * @since     1.0
   * @access    public
   */
  public $security_level;//セキュリティレベル

  private function isAjax() {
    if ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
      return true;
    } else {
      return false;
    }
  }

  public function begin_start() {
   global $url;
   global $noLoginCheck;
   $putlog = putlog::singleton();
    if ($url["filename"] == "track") {
     d("base_module.begin_start","クロスドメイン");
     header("Access-Control-Allow-Origin:*");
     header("Access-Control-Allow-Credentials:true");
    }
    if ($this->isAjax()) {
      d("base_module.begin_start","======AJAX======");
      $putlog->setLogMode("AJAX");
      d("begin_start","api=>".$this->param["ARGS"]["api"]);
    } else {
      d("base_module.begin_start","======WEB======");
      $putlog->setLogMode("WEB");
      d("test","career=>".$this->career);
      d("test","solid_number=>".$this->solid_number);
      $this->dispdt["head_area"]["jsTimestamp"] = SOURCE_VERSION;
      $this->dispdt["footer_area"]["jsTimestamp"] = SOURCE_VERSION;
    }

    $account = $this->ss->getVar("account");
    //d("begin_start noLoginCheck #",print_r($noLoginCheck,true));
    //d("begin_start filename #",print_r($url["filename"],true));
    //ログインチェックを行うページかチェックする
    $noLoginCheckStatus;
    if (in_array($url["filename"],$noLoginCheck) == TRUE) {
      //ログインチェックを行わない。
      //d("appController.begin_start","ログインチェックを行わない");
      $noLoginCheckStatus = TRUE;
    } else {
      //d("appController.begin_start","ログインチェックを行う");
      $noLoginCheckStatus = FALSE;
    }
    //ajaxかチェックする
    if ($this->common->isEmpty($this->param["ARGS"]["api"]) == true && $noLoginCheckStatus == FALSE) {
      d("base_module.begin_start","login account=>".$account["profile"]["profile_id"]);
      d("base_module.begin_start","filename=>".$url["filename"]);
      if ($url["filename"] != "index" && $url["filename"] != "track") {
       $ret = $this->common->loginCheck($this->db_slave,$this->ss,$this->param["ARGS"],$this->dispdt);
       if ($ret == false) {
        d("base_module.begin_start","logoff");
        header("Location:index.html?btn_event=logoff");
       }
      }
    } else if ($this->common->isEmpty($this->param["ARGS"]["api"]) == false) {
      //トークンチェック
    }

    $this->language = $account["player"]["language"];
    $this->security_level = $account["player"]["security_level"];

    //$this->putlog->d("begin_start","UserAgent:".$_SERVER['HTTP_USER_AGENT']);
    //$this->putlog->d("begin_start","career:".$this->career);
    //$this->putlog->d("begin_start","session_key:".$this->session_key);

    //$account = $this->mem->getVar("account");
    //$this->putlog->d("begin_start","login staff:".$account["staff_name"]);
  }

  public function begin_end() {
    //d("base_module.begin_end",print_r($this->dispdt,true));
  }

  protected function render($action="") {
    global $directory_access;
    global $url;

    if ($this->render_status) return;//2回目以降は通らない
    $this->render_status = true;
    d("base_module.render","action=>".$action);
    $html_dir = $directory_access;
    $obj = new tau();
    if ($action == "") {
      $obj->loadTemplatefile($url["filename"].".html",VIEWPATH);
    } else {
      $obj->loadTemplatefile($action.".html",VIEWPATH);
    }
    $this->dispdt = $obj->merge($this->dispdt,$data,$this->language);
    $obj->array_func($this->dispdt);

    echo $obj->tpl->get();

  }


  /**
   *  連想配列をjson形式に変換する
   *
   * @since     1.0
   * @param     array    $result   連想配列
   * @access    protected
   * @return    void
   */
  protected function toJson($result) {
    d("appController.toJson",print_r($result,true));
    $json = json_encode($result);
    $volm = strlen(bin2hex($json)) / 2;
    d("base_module.toJson ",$volm." byte");
    //d("base_module.toJson 圧縮前",$volm." byte");
    //d("base_module.toJson json","---------------------");
    //d("base_module.toJson","[".$json."]");
    //d("base_module.toJson json","---------------------");

    //$json = base64_encode(gzdeflate($json, 9));//圧縮
    //$volm = strlen(bin2hex($json)) / 2;
    //d("base_module.toJson 圧縮後",$volm." byte");

    $this->dispdt["BODY_AREA"]["json"] = $json;
  }

  /**
   *  更新系SQL実行
   *
   * @since     1.0
   * @param     string    $sql   SQL文
   * @access    protected
   * @return    void
   */
  protected function callBySql($sql) {
    try {
      return $this->db_master->Query($sql);
    } catch (Exception $e) {
      $this->putlog->error("callBySql",$e->getMessage(),__FILE__.":".__LINE__." ".__METHOD__);
      return false;
    }
  }

  /**
   *  複数検索
   *
   * @since     1.0
   * @param     string    $sql   SQL文
   * @access    protected
   * @return    void
   */
  protected function getResultList($sql,&$result) {
    try {
      $result = false;
      $result = $this->db_slave->QueryEx($sql);
      return $this;
    } catch (Exception $e) {
      $this->putlog->error("getResultList",$e->getMessage(),__FILE__.":".__LINE__." ".__METHOD__);
      return $this;
    }
  }

  /**
   *  1件検索
   *
   * @since     1.0
   * @param     string    $sql   SQL文
   * @access    protected
   * @return    void
   */
  protected function getSingleResult($sql,&$result) {
    try {
      $data = $this->db_slave->QueryEx($sql);
      $result = false;
      if ( is_array( $data ) && (sizeof($data) > 0) ) {
        $result = $data[0];
      }
      return $this;
    } catch (Exception $e) {
      $this->putlog->error("getSingleResult",$e->getMessage(),__FILE__.":".__LINE__." ".__METHOD__);
      return $this;
    }
  }

  public function master($p) {
    require_once(LIBPATH."mods/master.php");
    $m = new master();
    $m->common = &$this->common;
    $m->base_module = &$this;
    $m->db_master = &$this->db_master;
    $m->db_slave = &$this->db_slave;
    $m->jjStrConvert = &$this->mods["jjStrConvert"];

    if ($p["network"] == "state") {
     //ネットワークの疎通チェック
     d("base_module.network","state");
     return json_encode("OK");
    } else {
     return $m->get($p["tbl"],$p);
    }
  }

  public function message($no,$lang="") {
    $l = $this->language;
    if ($l != "ja" && $l != "en") {
      $l = "en";
    }
    if ($lang != "") $l = $lang;
    $data = $this->getLanguageMessage();
    return $data[$no][$l];
  }



}

function d($tag,$txt,$Loc="") {
  $putlog = putlog::singleton();
  $putlog->d($tag,$txt,$Loc);
}
function dd($tag,$txt,$Loc="") {
  $putlog = putlog::singleton();
  $putlog->logput($tag,$txt,$Loc);
}
function e($tag,$txt,$Loc="") {
  $putlog = putlog::singleton();
  $putlog->e($tag,$txt,$Loc);
}

// XSS
function h($str="") {
  if(is_array($str)) {
    $h = function_exists("h") ? "h" : array(&$this, "h");
    return array_map($h, $str);
  } else {
    if(!is_numeric($str)) {
      $str = htmlspecialchars($str, ENT_QUOTES, "UTF-8"); // 文字コードは適宜変更
    }
    return $str;
  }
}
// SQL Injection
/*
function q($data) {
 return str_replace ("'", "''", $data);
}
*/

function q($str="") {
  if(is_array($str)) {
    $q = function_exists("q") ? "q" : array(&$this, "q");
    return array_map($q, $str);
  } else {
    if(get_magic_quotes_gpc()) {
      $str = stripslashes($str);
    }
    if (!is_numeric($str)) {
      $str = str_replace ("'", "’", $str);
    }
    return $str;
  }
}


?>