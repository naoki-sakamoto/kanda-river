<?php
/**
 * ログを出力する
 *
 * @version   $Id: putlog.php,v 3.0
 * @access    public
 */
class putlog {
  private $debug;
  private $id;
  public $path_debug;
  //private $path_error;
  private $log_mode;//ログに記述するモード

  function __construct($_debug=false) {
    static $instance;


    $this->id = getmypid();
    //$error_log = ini_get("error_log");

    $this->path_debug = DEBUG_LOG_PATH;
    //$this->path_error = ERROR_LOG_PATH;

    //$this->cre_file($error_log);
    $this->cre_file($this->path_debug);
    //$this->cre_file($this->path_error);

    $this->debug=true;//$_debug;
    $instance = $this;
  }

  /**
   * シングルトン
   * @return  class   このクラス
   */
  static function singleton()
  {
    static $instance;
    if (!isset($instance)) {
      $instance = new putlog();
    }

    return $instance;
  }

  /**
   *  ファイルの存在を確認して、なければ空ファイルを作成する
   *
   * @since     1.0
   * @param     string    $path   ファイルパス
   * @access    public
   * @return    void
   */
  function cre_file($path) {
    if (is_file($path) == false) {
      touch($path);
      chmod($path,0777);//フル権限
      return 1;
    }
    return 0;
  }

  /**
   *  デバッグモードを設定する。
   *
   * @since     1.0
   * @param     bool    $_debug   true:デバッグモード
   * @access    public
   * @return    void
   */
  public function set_debug($_debug) {
    $this->debug = $_debug;
  }

  public function setLogMode($_mode) {
   $this->log_mode = $_mode;
  }

  public function d($tag,$txt,$Loc="") {
    $this->put($tag,$txt,$Loc);
  }
  public function e($tag,$txt,$Loc="") {
    $this->error($tag,$txt,$Loc);
  }

  public function put($tag,$txt,$Loc="") {
    if ($this->debug == true) {
      if ($tag != "") $tag = "[".$tag."]";
      if ($this->log_mode != "") $tag = "[".$this->log_mode."]".$tag;
      $day = date("Y/m/d H:i:s");
      $tag .= "[".$this->id."]";
      //error_log($tag." ".$Loc." ".$txt, 0);
      file_put_contents($this->path_debug, "[".$day."]".$tag." ".$Loc." ".$txt."\n",FILE_APPEND);
    }
  }

  public function error($tag,$txt,$Loc="") {
    if ($tag != "") $tag = "[".$tag."]";
    $day = date("Y/m/d H:i:s");
    $tag .= "[".$this->id."]";
    //error_log($tag." ".$Loc." ".$txt, 0);
    //file_put_contents($this->path_error, "[".$day."]".$tag." ".$Loc." ".$txt."\n",FILE_APPEND);
    file_put_contents($this->path_debug, "[".$day."]"."[ERROR]".$tag." ".$Loc." ".$txt."\n",FILE_APPEND);
    //$this->put($tag,$txt,$Loc);
  }

  public function logput($tag,$txt,$Loc="") {
    if ($tag != "") $tag = "[".$tag."]";
    $tag .= "[".$this->id."]";
    //error_log($tag." ".$Loc." ".$txt, 0);
    file_put_contents($this->path_debug, $tag." ".$Loc." ".$txt."\n",FILE_APPEND);
  }
}
?>