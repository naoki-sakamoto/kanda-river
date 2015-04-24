<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * セッションレベル
 *
 * @const     HSLV_FOREVER    セッションレベル
 */
define('HSLV_FOREVER', 0);

/**
 * セッション管理クラス
 *
 * セッション管理クラス。
 * 複数ページ間でデータを引き継ぎたい場合に、セッション変数として保持が可能。
 * セッションが切断されるまでデータは保持される。
 *
 * @version   $Id: modSession.php,v 2.0
 * @access    public
 */

class modSession {
  public $prefix = "kvs";
  /**
  * セッションを開始する。
  *
  * セッションを開始する。
  *
  * @since     1.0
  * @access    public
  * @return    void
  */
  function SessionStart($career,$solid_number) {
    //セッションのIDを生成する。
    if ($career == "") $career = "pc";
    if ($career == "pc") {
      if ( $_COOKIE['PHPSESSID'] == '' ) {
        list( $usec, $sec ) = explode(' ', microtime() );
        $seed = (float) $sec + ( (float) $usec * 100000 );

        mt_srand( $seed );
        $tmp_data = $_SERVER['SERVER_ADDR']."$".uniqid( mt_rand(), 1 );
        session_id( md5( $tmp_data ) );
      }
    } else {
      if ($solid_number == "") {
        $tmp_data = md5($_SERVER['SERVER_ADDR']."$".uniqid(rand(), true));
      } else {
        $tmp_data = $solid_number;
      }
      session_id( md5( $tmp_data ) );
    }

    session_start();
  }

  function get_session_id() {
    return session_id();
  }


  /**
  * セッション破棄
  *
  * セッションを破棄する。
  *
  * @since     1.0
  * @access    public
  * @return    void
  */
  function destroy() {
    session_destroy();
  }

  /**
  * セッション変数設定
  *
  * セッション変数の設定をする。
  *
  * @param     string    $name     セッション変数名
  * @param     string    $val      セッション格納値
  * @param     string    $level    セッションレベル（オプション）
  * @since     1.0
  * @access    public
  * @return    void
  */
  function setVar( $name, $val, $level =HSLV_FOREVER ) {
  //      session_register( $name );
    $_SESSION[$this->prefix][$name] = array( $val, $level );
  }

  /**
  * セッション変数取得
  *
  * セッション変数の取得をする。
  *
  * @param     string    $name     セッション変数名
  * @since     1.0
  * @access    public
  * @return    string
  */
  function getVar( $name ) {
    list( $val, $level ) = $_SESSION[$this->prefix][$name];
    return $val;
  }

  /**
  * セッションレベル取得
  *
  * セッションレベルの取得をする。
  *
  * @param     string    $name     セッション変数名
  * @since     1.0
  * @access    public
  * @return    int
  */
  function getLevel( $name ) {
    list( $val, $level ) = $_SESSION[$this->prefix][$name];
    return $level;
  }

  /**
  * セッション変数確認
  *
  * セッション変数の存在確認をする。
  *
  * @param     string    $name     セッション変数名
  * @since     1.0
  * @access    public
  * @return    boolean
  */
  function issetVar( $name ) {
    //return session_is_registered( $name );
    return isset( $_SESSION[$this->prefix][$name] );
  }

  /**
  * セッション変数確認
  *
  * 変数がセッションに登録されるかどうか確認する。
  *
  * @since     1.0
  * @access    public
  * @return    boolean
  */
  function isAvail() {
    return session_is_registered( $name );
  }

  /**
  * セッション変数削除
  *
  * セッション変数の削除をする。
  *
  * @param     string    $name     セッション変数名
  * @since     1.0
  * @access    public
  * @return    boolean
  */
  function unsetVar( $name ) {
    //session_unregister( $name );
    unset($_SESSION[$this->prefix][$name]);
  }

  /**
  * セッション変数一斉削除（レベル毎の削除）
  *
  * セッション変数を指定されたレベル毎に削除する。
  *
  * @param     string    $level     レベル
  * @since     1.0
  * @access    public
  * @return    boolean
  */
  function unsetLevelAll( $level ) {
    if ( isset( $_SESSION[$this->prefix] ) ) {
      foreach ( $_SESSION[$this->prefix] as $name => $pair ) {
        list( $val, $lvl ) = $pair;
        if ( $lvl == $level ) {
          $this->unsetVar( $name );
        }
      }
    }
  }

  /**
  * セッション変数一斉削除
  *
  * セッション変数を一斉に削除する。
  *
  * @since     1.0
  * @access    public
  * @return    boolean
  */
  function unsetAll( ) {
    if ( isset( $_SESSION[$this->prefix] ) ) {
      unset($_SESSION[$this->prefix]);
      /*
      foreach ( $_SESSION[$this->prefix] as $name => $pair ) {
        $this->unsetVar( $name );
      }
      */
    }
  }

}

?>