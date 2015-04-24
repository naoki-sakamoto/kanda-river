<?php
class common {
  public $putlog;

  /**
   *  ログインチェック
   *
   * @since     1.0
   * @param     object   $db   データベースオブジェクト
   *            array    $ss   セッション変数
   *            array    $param   パラメータ変数
   *            array    $dispdt   画面表示オブジェクト
   * @access    public
   * @return    void
   */
  public function loginCheck(&$db,&$ss,$param,&$dispdt) {
    $account = $ss->getVar("account");
    //d("common.loginCheck","account=>".print_r($account,true));
    d("common.loginCheck","loginStatus=>".$account["profile"]["loginStatus"]);

    if ($this->isEmpty($account["profile"]["loginStatus"])) {
      d("common.loginCheck","No!");
      return false;
    } else {
      if ($account["profile"]["loginStatus"] == true) {
        d("common.loginCheck","Yes!!");
      } else {
        d("common.loginCheck","No!!");
        return false;
      }
    }

    //d("common.loginCheck","account=>".print_r($account,true));
    $data = $this->get_profile($db,$account["profile"]["profile_id"]);
    d("common.loginCheck","name=>".$data["profile_name"]);
    //d("common.loginCheck","player=>".print_r($player,true));
    $global_area = <<<EOT
   <input type="hidden" name="g_competition_id" id="g_competition_id" value="{$data["competition_id"]}">
   <input type="hidden" name="g_team_id" id="g_team_id" value="{$data["team_id"]}">
   <input type="hidden" name="g_position_id" id="g_position_id" value="{$data["position_id"]}">
   <input type="hidden" name="g_profile_id" id="g_profile_id" value="{$data["profile_id"]}">
   <input type="hidden" name="g_security_level" id="g_security_level" value="{$data["security_level"]}">
   <input type="hidden" name="token" id="token" value="{$this->get_token()}">
EOT;
    $dispdt["body_area"]["login_player_name"] = $data["profile_name"];
    $dispdt["body_area"]["global_area"]["hidden"] = $global_area;
    return true;
  }

  private function token_check($token1,$token2) {
    if ($token1 == $token2) {
      return true;
    } else {
      $ret = array();
      $ret["warning"] = "原因不明のエラーが発生しました。恐れ入りますが最初からやり直してください。";
      $ret["btn"] = "戻る";
      return $ret;
    }
  }

  public function login_check($account,$page_power = 20) {
    if ($account["account_id"] == "") {
      header("Location:http://www.ttm.ms/admin/index.html?btn_event=session_timeout");
      exit;
    }

    if ($account["account_power"] > $page_power) {
      header("Location:http://www.ttm.ms/admin/index.html?btn_event=power");
      exit;
    }
    return true;
  }


  /**
   * UTF-8文字列を指定のエンコードに変換する
   * @param	string $utf8 UTF-8文字列
   * @return	string 変換後文字列
  */
  public function utf82encode($txt, $mozicode) {
    return mb_convert_encoding($txt, $mozicode, 'UTF-8');
  }

  /**
   * SQLスペシャルキャラ一括変換処理
   *
   * シングルクォートを付加する。
   *
   * @param     string    $data     データ
   * @since     1.0
   * @access    public
   * @return    string
   */
  public function convSQLSpChar( $data ) {
    return str_replace ("'", "''", $data);
  }

  public function get_date(&$db) {
    $sql = <<<EOT
      SELECT sysdate() as dt;
EOT;
    $data = $db->QueryEx($sql);

    if ( is_array( $data ) && (sizeof($data) > 0) ) {
      return $data[0]["dt"];
    } else {
      return false;
    }
  }

  /**
   *  一意なトークンを発行
   *
   * @since     1.0
   * @param
   * @access    public
   * @return    string
   */
  public function get_token() {
    return md5($_SERVER['SERVER_ADDR']."$".uniqid(rand(), true));
  }

  /**
   *  文字列がnullまたは空文字列ならtrueを返します。
   *
   * @since     1.0
   * @param     string    $text 文字列
   * @access    public
   * @return    bool  文字列がnullまたは空文字列ならtrue
   */
  public function isEmpty($text) {
    if (isset($text) === false) return true;
    if ($text == "") return true;
    if ($text === NULL) return true;
    return false;
  }

  /**
   *  クライアントのIPアドレスを返す
   *
   * @since     1.0
   * @param
   * @access    public
   * @return    array["proxy"]  プロキシサーバーIPアドレス
   *            array["ip"]     クライアントIPアドレス
   *            array["host"]   ホスト名
   */
  public function get_client_ip_address() {
    $ret["proxy"] = "";
    $ret["ip"] = "";
    $ret["host"] = "";
    try {
      if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
        if ($_SERVER["HTTP_CLIENT_IP"]) {
          $ret["proxy"] = $_SERVER["HTTP_CLIENT_IP"];
        } else {
          $ret["proxy"] = $_SERVER["REMOTE_ADDR"];
        }
        $ret["ip"] = $_SERVER["HTTP_X_FORWARDED_FOR"];
      } else {
        if ($_SERVER["HTTP_CLIENT_IP"]) {
          $ret["ip"] = $_SERVER["HTTP_CLIENT_IP"];
        } else {
          $ret["ip"] = $_SERVER["REMOTE_ADDR"];
        }
      }

      if ($ret["ip"] != "") {
        $ret["host"] = @gethostbyaddr($ret["ip"]);
      }

      return $ret;
    } catch (Exception $e) {
      return $ret;
    }
  }

  /**
   *  バッチプログラムの実行時間を更新する
   *
   * @since     1.0
   * @param     string    $XXXX   YYYYYY
   * @access    public
   * @return    void
   */
  public function up_batch_management_status(&$db,$program_name) {
    try {
      $sql = <<<EOT
        update batch_management set
         status = sysdate()
        WHERE
         program_name = '{$program_name}'
        ;
EOT;
      return $db->Query($sql);
    } catch (Exception $e) {
      $this->putlog->error("common.up_twitter_account",$e->getMessage(),__FILE__.":".__LINE__." ".__METHOD__);
      return false;
    }
  }

  /**
   *  バッチプログラムの前回実行時間を返す
   *
   * @since     1.0
   * @param     string    $XXXX   YYYYYY
   * @access    public
   * @return    void
   */
  public function get_batch_management(&$db,$program_name) {
    try {
      $sql = <<<EOT
select
 *
FROM
 batch_management
where
  program_name = '{$program_name}';
EOT;

    $data = $db->QueryEx($sql);

    if ( is_array( $data ) && (sizeof($data) > 0) ) {
      $ret = $data[0];
    } else {
      $ret = "";
    }

      return $ret;
    } catch (Exception $e) {
      $this->putlog->error("common.get_batch_management",$e->getMessage(),__FILE__.":".__LINE__." ".__METHOD__);
      return false;
    }
  }


  /**
   * file_get_contents()関数でPOSTする。
   * @param string $url リクエストURL
   * @param array  $params パラメータの連想配列
   * @return string レスポンスボディ
   */
  function doPost($url, $params) {
    $headers = array(
      'Content-Type: application/x-www-form-urlencoded',
      'ssl_verify_peer: false',
    );
    $requestOptions = array(
      'http' => array(
        'method'  => 'POST',
        'header'  => implode('\r\n', $headers),
        'content' => http_build_query($params)
      )
    );
    return file_get_contents($url, false,
      stream_context_create($requestOptions)
    );
  }

  /**
   * ファイル名の禁止文字を除外する
   *
   * @since     1.0
   * @param     string    $name
   * @access    public
   * @return    void
   */
  public function ConvertFileName($name) {
   $name = str_replace(":", "", $name);
   $name = str_replace(";", "", $name);
   $name = str_replace("/", "", $name);
   $name = str_replace("|", "", $name);
   $name = str_replace(",", "", $name);
   $name = str_replace("*", "", $name);
   $name = str_replace("?", "", $name);
   $name = str_replace('"', "", $name);
   $name = str_replace("<", "", $name);
   $name = str_replace(">", "", $name);
   $name = str_replace('\\', "", $name);
   $name = str_replace('.', "", $name);
   $name = str_replace(' ', "", $name);
   return $name;
  }

  /**
   *  配列に入力されたメッセージをpタグで囲ったメッセージに変換
   *
   * @since     1.0
   * @param     array    $msg   配列に入力されたメッセージ
   * @access    public
   * @return    string
   */
  public function array2msg($msg) {
    $ret = "";
    if ( is_array( $msg ) && (sizeof($msg) > 0) ) {
      foreach( $msg as $key=>$val ) {
        $ret .= "<p>".$val."</p>\n";
      }
    }
    return $ret;
  }

  /**
   * 改行コードをBRタグに変換する
   *
   * @since     1.0
   * @param     string    $txtX   テキスト
   * @access    public
   * @return    void
   */
  public function LF2br($txt) {
   if ($txt == "") return "";
   $txt = str_replace("\r\n", "\n", $txt);
   $txt = str_replace("\r", "\n", $txt);
   $txt = str_replace("\n", "<br>", $txt);
   return $txt;
  }

  /**
   * 数字のクリーニング
   *
   * @since     1.0
   * @param     string    $val   値
   * @access    public
   * @return    int
   */
  public function number_cleaning($val) {
   return str_replace(",","",$val);
  }
  /**
   * 桁を揃える
   *
   * @since     1.0
   * @param     string    $val   値
   * @param     string    $precision   桁数
   * @access    public
   * @return    string
   */
  public function format($val,$precision=0) {
   if ($this->isEmpty($val) == true) $val = 0;
   return number_format($val,$precision);
  }

}
?>