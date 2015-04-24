<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * モバイル用クラス
 *
 * モバイル用クラス
 * 
 *
 * [ 修正履歴 ]
 * <ul>
 * <li>2004/01/07   M.Mizuno    新規作成<li>
 * <li>2004/02/12   M.Mizuno    IPアドレス帯域チェックの追加（各キャリア毎）<li>
 * <li>2004/03/25   M.Mizuno    IPアドレス帯域チェックなどの変数を移動<li>
 * <li>2004/06/08   sawaguchi   IPアドレス帯域追加 AU(http://au.kddi.com/ezfactory/tec/spec/ezsava_ip.html)<li>
 * <li>2004/10/07   Y.Kagawa    IPアドレス帯域追加 AU(222.5.63.0/24)<li>
 * <li>2005/01/17   sawaguchi   キャリアの判定の修正（ボーダフォンになった）<li>
 * <li>2005/02/17   sawaguchi   キャリアの判定の修正（モトローラ）<li>
 * <li>2005/04/27   sawaguchi   IPアドレス帯域追加 AU(219.125.149.0/24)<li>
 * <li>2005/07/11   Y.Kagawa    IPアドレス帯域追加 Vodafone(210.146.60.192/26,210.151.9.128/26,210.169.176.0/24,210.175.1.128/25)<li>
 * <li>2006/05/12   sawaguchi   IPアドレス帯域追加 AU(222.5.62.0/24 222.7.57.0/24)<li>
 * <li>2006/06/16   sawaguchi   IPアドレス帯域追加 Vodafone(202.179.204.0/24)<li>
 * <li>2006/06/23   sawaguchi   IPアドレス帯域見直し AUページより<li>
 * <li>2006/06/28   sawaguchi   IPアドレス帯域追加 docomo(210.153.86.0/24),AU(59.135.38.0/24)<li>
 * <li>2006/09/29   m.mizuno    キャリアの判定の修正（vodafone→Softbank）<li>
 * <li>2006/10/25   sawaguchi   IPアドレス帯域見直し※ezすべてサブネットマスクを0/24にした（仮）<li>
 * <li>2006/11/07   sawaguchi   IPアドレス帯域見直し ezの帯域設定を絞った<li>
 * <li>2006/11/07   sawaguchi   IPアドレス帯域追加 AU(219.108.157.0/25)、.htaccess<li>
 * <li>2006/11/07   sawaguchi   IPアドレス帯域追加 docomo(メールサーバ)、.htaccess<li>
 * <li>2006/12/20   sawaguchi   IPアドレス帯域追加 SoftBank<li>
 * <li>2007/01/09   sawaguchi   IPアドレス帯域追加 AU(219.125.151.0/24)、.htaccess<li>
 * <li>2007/06/13   sawaguchi   IPアドレス帯域追加 AU(219.125.145.0/24)、.htaccess<li>
 * <li>2007/06/28   M.Tajima    固体識別番号取得処理を追加<li>
 * <li>2007/08/08   M.Mizuno    機種名取得処理を追加<li>
 * <li>2011/09/22   n.sakamoto  Android,iPad,iPodを追加<li>
 * </ul>
 *
 *
 * @package   Signal
 * @author    Manabu Mizuno <mizuno@signalbase.co.jp>
 * @version   $Id: mobile.php,v 1.03 2004/01/07 mizuno Exp $
 * @access    public
 * @copyright 2004-2006 signalbase Inc.
 */

//require_once('Config.php');

//define( "BASE_PATH", "/usr/local/lib/php/" );

class mobile {

  /**
   * ユーザーエージェント
   *
   * @var       string
   * @since     1.0
   * @access    private
   */
  var $user_agent = '';

  /**
   * エラー内容
   *
   * @var       array
   * @since     1.0
   * @access    public
   */
  var $error = array();
  
  /**
   * ビットマスクとサブネット
   *
   * @var       array
   * @since     1.0
   * @access    private
   */
  var $Netmask_Map = array();

  /**
   * IPアドレス帯域（i-mode）
   *
   * @var       array
   * @since     1.0
   * @access    private
   */
  var $IP_Band_i = array();

  /**
   * IPアドレス帯域（SoftBank）
   *
   * @var       array
   * @since     1.0
   * @access    private
   */
  var $IP_Band_s = array();

  /**
   * IPアドレス帯域（EZweb）
   *
   * @var       array
   * @since     1.0
   * @access    private
   */
  var $IP_Band_e = array();

  /**
   * コンストラクター
   *
   * @since     1.0
   * @access    public
   * @return    void
   */
  function mobile() {
    
    $this->user_agent = '';
    $this->info  = array();
    $this->error = array();
/*
    //--- ダンプイメージファイルの存在チェック ---
    if ( ! file_exists( BASE_PATH."Signal/conf/ip_map.dmp" ) ) {
      //--- Configクラスの初期化 ---
      $config = new Config();        

      //--- 設定ファイルの読み込み ---
      $parseConf =& $config->parseConfig( BASE_PATH."Signal/conf/ip_map.xml", 'xml' );
      $ip_map = $parseConf->toArray();
    }else{
      //--- 設定ファイルの読み込み ---
      $ip_map = unserialize( file_get_contents( "Signal/conf/ip_map.dmp", true ) );
    }

    $this->Netmask_Map = $ip_map["root"]["ip"]["netmask_map"];
    $this->IP_Band_i   = $ip_map["root"]["ip"]["ip_band_i"];
    $this->IP_Band_e   = $ip_map["root"]["ip"]["ip_band_e"];
    $this->IP_Band_s   = $ip_map["root"]["ip"]["ip_band_s"];
*/
  }
  
  /**
   * ユーザーエージェントの設定をする。
   *
   * @param     string    $data
   * @since     1.0
   * @access    public
   * @return    void
   */
  function setUserAgent( $data ) {

    $this->user_agent = $data;
    
  }

  /**
   * キャリア判定
   *
   * ユーザーエージェント情報よりキャリアの判定を行う。
   *
   *<pre>
   *  [例]
   *    $mbl = new mobile();
   *    $mbl->setUserAgent($_SERVER["USER_AGENT"]);
   *    switch ( $mbl->getCareer() ) {
   *      case 'i':
   *        //i-mode処理
   *        break;
   *      case 's':
   *        //SoftBank処理
   *        break;
   *      case 'e':
   *        //ezweb処理
   *        break;
   *      default:
   *        //その他処理
   *        break;
   *    }
   *</pre>
   *
   * @since     1.0
   * @access    public
   * @return    string  (i:i-mode/s:SoftBank/e:ezweb/h:H"/d:doti)
   * @see       setUserAgent()
   */
  function getCareer() {
    
    if (       preg_match( "/DoCoMo/",      $this->user_agent ) ) {   //i-mode FOMA
      if ( ! $this->chkIpBand_i( $_SERVER["REMOTE_ADDR"] ) ) {
        return '';
      }
      return 'i';
    } elseif ( preg_match( "/SoftBank/",     $this->user_agent ) ) {   //vodafone→SoftBank - 2006/09/29
      if ( ! $this->chkIpBand_s( $_SERVER["REMOTE_ADDR"] ) ) {
        return '';
      }
      return 's';
    } elseif ( preg_match( "/iPhone/",     $this->user_agent ) ) {   //iPhone - 2009/07/08
      return 'iPhone';
    } elseif ( preg_match( "/Vodafone/",     $this->user_agent ) ) {   //vodafone
      if ( ! $this->chkIpBand_s( $_SERVER["REMOTE_ADDR"] ) ) {
        return '';
      }
      return 's';
    } elseif ( preg_match( "/J-PHONE/",     $this->user_agent ) ) {   //vodafone(J-PHONE)
      if ( ! $this->chkIpBand_s( $_SERVER["REMOTE_ADDR"] ) ) {
        return '';
      }
      return 's';
    } elseif ( preg_match( "/MOT-/",     $this->user_agent ) ) {   //vodafone(MOTOROLA)
      if ( ! $this->chkIpBand_s( $_SERVER["REMOTE_ADDR"] ) ) {
        return '';
      }
      return 's';
    } elseif ( preg_match( "/UP\.Browser/", $this->user_agent ) ) {   //au tuka
      if ( ! $this->chkIpBand_e( $_SERVER["REMOTE_ADDR"] ) ) {
        return '';
      }
      return 'e';
    } elseif ( preg_match( "/PDXGW/",       $this->user_agent ) ) {   //H"
      return 'h';
    } elseif ( preg_match( "/ASTEL/",       $this->user_agent ) ) {   //doti
      return 'd';
    } elseif ( preg_match( "/Android/",       $this->user_agent ) ) {   //Android - 2011/09/22
      return 'Android';
    } elseif ( preg_match( "/iPad/",       $this->user_agent ) ) {   //iPad - 2011/09/22
      return 'iPad';
    } elseif ( preg_match( "/iPod/",       $this->user_agent ) ) {   //iPod - 2011/09/22
      return 'iPod';
    } else {
      return '';
    }

  }

  /**
   * i-mode絵文字判定
   *
   * 指定された文字列にi-mode絵文字が含まれているか判定を行う。
   *
   *<pre>
   *  [例]
   *    $mbl = new mobile();
   *    if ( $mbl->chkEmoji_i( $data ) ) {
   *      //絵文字がある場合の処理
   *    } else {
   *      //絵文字がない場合の処理
   *    }
   *</pre>
   *
   * @param     string    $data
   * @since     1.0
   * @access    public
   * @return    boolen    true:絵文字あり false:絵文字なし
   */
  function chkEmoji_i( $data ) {
    
    $str = unpack( "C*", $data );
    $len = count( $str );
    $n = 1;
    while ( $n <= $len ) {
      $ch1 = $str[$n];
      $ch2 = $str[$n+1];
      if ( ($ch1 == 0xF8) && (0x9F <= $ch2) && ($ch2 <= 0xFC) ) {
        return true;
      } elseif (($ch1 == 0xF9) &&
               ((0x40 <= $ch2) && ($ch2 <= 0x49) ||
                (0x50 <= $ch2) && ($ch2 <= 0x52) ||
                (0x55 <= $ch2) && ($ch2 <= 0x57) ||
                (0x5B <= $ch2) && ($ch2 <= 0x5E) ||
                (0x72 <= $ch2) && ($ch2 <= 0x7E) ||
                (0x80 <= $ch2) && ($ch2 <= 0xFC))) {
        return true;
      }
      $n++;
    }
    return false;
  }

  /**
   * SoftBank絵文字判定
   *
   * 指定された文字列にSoftBank絵文字が含まれているか判定を行う。
   *
   *<pre>
   *  [例]
   *    $mbl = new mobile();
   *    if ( $mbl->chkEmoji_s( $data ) ) {
   *      //絵文字がある場合の処理
   *    } else {
   *      //絵文字がない場合の処理
   *    }
   *</pre>
   *
   * @param     string    $data
   * @since     1.0
   * @access    public
   * @return    boolen    true:絵文字あり false:絵文字なし
   */
  function chkEmoji_s( $data ) {
    
    if ( preg_match( "/\x1B[\x24][\x21-\x7A]{2,}\x0F/", $data ) ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * ezweb絵文字判定
   *
   * 指定された文字列にezweb絵文字が含まれているか判定を行う。
   *
   *<pre>
   *  [例]
   *    $mbl = new mobile();
   *    if ( $mbl->chkEmoji_e( $data ) ) {
   *      //絵文字がある場合の処理
   *    } else {
   *      //絵文字がない場合の処理
   *    }
   *</pre>
   *
   * @param     string    $data
   * @since     1.0
   * @access    public
   * @return    boolen    true:絵文字あり false:絵文字なし
   */
  function chkEmoji_e( $data ) {
    
    if ( preg_match( "/<img\s+(icon|localsrc)\s*=\s*\"?[0-9]+\"?\s*\/*>/i", $data ) ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * i-mode絵文字変換
   *
   * i-mode絵文字をHTML表記可能なコードに変換する。
   * (i-mode基本絵文字を10進数SJIS表記に、拡張絵文字を16進数Unicode表記に変換する。)
   *
   *<pre>
   *  [例]
   *    $mbl = new mobile();
   *    $str = $mbl->encEmoji_i( $data ) );
   *</pre>
   *
   * @param     string    $data   (変換前の文字列)
   * @param     string    $option (デフォルトはtrue。falseを指定すると削除)
   * @since     1.0
   * @access    public    
   * @return    string    変換後の文字列
   */
  function encEmoji_i( $data, $option=true ) {
    
    $data = unpack( "C*", $data );
    $len  = count( $data );
    $buff = "";
    $n = 1;

    while ( $n <= $len ) {
      $ch1 = $str[$n];
      $ch2 = $str[$n+1];
      if ( (($ch1 == 0xF8) && (0x9F <= $ch2) && ($ch2 <= 0xFC)) ||
           (($ch1 == 0xF9) &&
           ((0x40 <= $ch2) && ($ch2 <= 0x49) ||
            (0x50 <= $ch2) && ($ch2 <= 0x52) ||
            (0x55 <= $ch2) && ($ch2 <= 0x57) ||
            (0x5B <= $ch2) && ($ch2 <= 0x5E) ||
            (0x72 <= $ch2) && ($ch2 <= 0x7E) ||
            (0x80 <= $ch2) && ($ch2 <= 0xB0))) ) {
        if ( $option ) {
          $buff .= '&#'.strval(($ch1 << 8) + $ch2).';';
          $n++;
        }
      } elseif ( ($ch1 == 0xF9) && (0xB1 <= $ch2) && ($ch2 <= 0xFC) ) {
          if ( $option ) {
            $buff .= '&#x'.strtoupper(dechex(0x7E00 + $ch2 - 165)).';';
            $n++;
          }
      } else {
          $buff .= pack( "C", $data[$n] );
          $n++;
      }
    }
    return $buff;
  }

  /**
   * SoftBank絵文字変換
   *
   * SoftBank絵文字をHTML表記可能なコードに変換する。
   *
   *<pre>
   *  [例]
   *    $mbl = new mobile();
   *    $str = $mbl->encEmoji_s( $data ) );
   *</pre>
   *
   * @param     string    $data   (変換前の文字列)
   * @param     string    $option (デフォルトはtrue。falseを指定すると削除)
   * @since     1.0
   * @access    public    
   * @return    string    変換後の文字列
   */
  function encEmoji_s( $data, $option=true ) {
    
    $data = preg_split( "/\x1B[\x24]([\x21-\x7A]{2,})\x0F/", $data, -1, PREG_SPLIT_DELIM_CAPTURE );
    $line = count( $data );
    $buff = "";
    $n = 0;

    while ( $n < $line ) {
      if ( $n % 2 ) {
        if ( $option ) {
          $buff .= '&#27;$'.$data[$n].'&#15;';
        }
      } else {
        $buff .= $data[$n];
        $n++;
      }
    }

    return $buff;
  }

  /**
   * ezweb絵文字削除
   *
   * 指定された文字列のezweb絵文字を削除する。
   *
   *<pre>
   *  [例]
   *    $mbl = new mobile();
   *    $str = $mbl->delEmoji_e( $data ) );
   *</pre>
   *
   * @param     string    $data   (変換前の文字列)
   * @since     1.0
   * @access    public    
   * @return    string    変換後の文字列
   */
  function delEmoji_e( $data ) {
    
    $data = preg_split("/<img\s+(icon|localsrc)\s*=\s*\"?[0-9]+\"?\s*\/*>/i", $data);
    return implode( "", $data );
  }

  /**
   * IP数値変換
   *
   * IPアドレスを数値化する。
   *
   *
   * @param     string    $ip   (IPアドレス)
   * @since     1.0
   * @access    public    
   * @return    integer    変換後の値
   */
  function cnvIp2Bit( $ip ) {
    
    $x = explode( '.', $ip );
    return ($x[0] << 24) | ($x[1] << 16) | ($x[2] << 8) | $x[3];
  }

  /**
   * アドレス帯域チェック（i-mode）
   *
   * IPアドレスが指定された帯域のものかチェックを行う。
   *
   *
   * @param     string    $ip   (IPアドレス)
   * @since     1.0
   * @access    public    
   * @return    boolean   
   */
  function chkIpBand_i( $ip ) {
  
    //global $Netmask_Map;
    //global $IP_Band_i;
    
    $ng_flg = 0;
    
    $adr = $this->cnvIp2Bit( $ip );
    
    foreach ( $this->IP_Band_i as $ip_addr ) {
      list( $net, $mask ) = explode( '/', $ip_addr );

      $net  = $this->cnvIp2Bit( $net );
      $mask = $this->cnvIp2Bit( $this->Netmask_Map[$mask] );

      if ( ( $adr & $mask ) != ( $net & $mask ) ) {
        $ng_flg = 1;
      } else {
        $ng_flg = 0;
        break;
      }
    
    }
    
    if ( $ng_flg == 1 ) {
      return false;
    } else {
      return true;
    }
    
  }

  /**
   * アドレス帯域チェック（SoftBank）
   *
   * IPアドレスが指定された帯域のものかチェックを行う。
   *
   *
   * @param     string    $ip   (IPアドレス)
   * @since     1.0
   * @access    public    
   * @return    boolean   
   */
  function chkIpBand_s( $ip ) {
  
    //global $Netmask_Map;
    //global $IP_Band_s;
    
    $ng_flg = 0;
    
    $adr = $this->cnvIp2Bit( $ip );
    
    foreach ( $this->IP_Band_s as $ip_addr ) {

      list( $net, $mask ) = explode( '/', $ip_addr );
      $net  = $this->cnvIp2Bit( $net );
      $mask = $this->cnvIp2Bit( $this->Netmask_Map[$mask] );

      if ( ( $adr & $mask ) != ( $net & $mask ) ) {
        $ng_flg = 1;
      } else {
        $ng_flg = 0;
        break;
      }
    
    }
    
    if ( $ng_flg == 1 ) {
      return false;
    } else {
      return true;
    }
    
  }

  /**
   * アドレス帯域チェック（ezweb）
   *
   * IPアドレスが指定された帯域のものかチェックを行う。
   *
   *
   * @param     string    $ip   (IPアドレス)
   * @since     1.0
   * @access    public    
   * @return    boolean   
   */
  function chkIpBand_e( $ip ) {
  
    //global $Netmask_Map;
    //global $IP_Band_e;
    
    $ng_flg = 0;
    
    $adr = $this->cnvIp2Bit( $ip );
    
    foreach ( $this->IP_Band_e as $ip_addr ) {

      list( $net, $mask ) = explode( '/', $ip_addr );
      $net  = $this->cnvIp2Bit( $net );
      $mask = $this->cnvIp2Bit( $this->Netmask_Map[$mask] );

      if ( ( $adr & $mask ) != ( $net & $mask ) ) {
        $ng_flg = 1;
      } else {
        $ng_flg = 0;
        break;
      }
    
    }
    
    if ( $ng_flg == 1 ) {
      return false;
    } else {
      return true;
    }
    
  }

  /**
   * 固体識別番号の取得
   *
   * 固体識別番号を取得する
   *
   *
   * @param     string    $
   * @since     1.0
   * @access    public    
   * @return    boolean   
   */
  function getSolidNumber() {

    $solid_number = "";

    switch( $this->getCareer() ) {
      //DoCoMo
      case "i":
        //iモードIDの取得
        $solid_number = $_SERVER['HTTP_X_DCMGUID'];
        if ($solid_number != "") break;
        //FOMA端末製造番号の取得
        if ( preg_match( "/ser(\w{15})/", $this->user_agent, $match ) ) {
          $tmp_number = $match[1];
          //FOMAカード製造番号の取得
          if ( preg_match( "/icc(\w{20})/", $this->user_agent, $match ) ) {
            $tmp_number = $match[1];
          }
          $solid_number = $tmp_number;

        //MOVA端末製造番号の取得
        } else if ( preg_match( "/ser(\w{11})/", $this->user_agent, $match ) ) {
          $solid_number = $match[1];
        }
        break;
      //AU
      case "e":
        $solid_number = $_SERVER["HTTP_X_UP_SUBNO"];
        break;
      //SoftBank
      case "s":
        if ( preg_match( "/.*\/.*\/.*\/SN(\w+)\s/", $this->user_agent, $match ) ) {
          $solid_number = $match[1];
        }
        break;
      //その他
      default:
        $solid_number = "";
        break;
    }

    return $solid_number;

  }

  /**
   * 携帯機種名の取得
   *
   * ユーザーエージェント等より機種名を取得する
   *
   *
   * @param     string    
   * @since     1.0
   * @access    public    
   * @return    string    機種名
   */
  function getDevicelName() {

    $device = "";
    $agent  = $this->user_agent;

    switch( $this->getCareer() ) {
      //DoCoMo
      case "i":
        //-- Mova機種判別
        list( , , $device, ) = explode( "/", $agent );
        //-- FOMA機種判別
        if ( $device == "" ) {
          if ( preg_match( "/^DoCoMo\/2\.0\s([0-9a-zA-Z]+)/", $agent, $match ) ) {
            $device = $match[1];
            if ( $device == 'MST' ) {
              $device = 'SH2101V';
            }
          } else {
            $device = 'DoCoMo';
          }
        }
        break;
      //AU
      case "e":
        $device = substr( $agent, ( strpos( $agent, "-" ) + 1 ), ( strpos( $agent, " " ) - strpos( $agent, "-" ) - 1 ) );
        break;
      //SoftBank
      case "s":
        $device = $_SERVER['HTTP_X_JPHONE_MSNAME'];
        break;
      //その他
      default:
        $device = "";
        break;
    }

    return $device;

  }

  /** 
   *  携帯ドメインを返す
   * 
   * @since     1.0
   * @access    public
   * @param     
   * @return    
   */
  function celldomain() {
    $ret[0] = "docomo.ne.jp";
    $ret[1] = "ezweb.ne.jp";
    $ret[2] = "disney.ne.jp";
    $ret[3] = "softbank.ne.jp";
    $ret[4] = "d.vodafone.ne.jp";
    $ret[5] = "h.vodafone.ne.jp";
    $ret[6] = "t.vodafone.ne.jp";
    $ret[7] = "c.vodafone.ne.jp";
    $ret[8] = "k.vodafone.ne.jp";
    $ret[9] = "r.vodafone.ne.jp";
    $ret[10] = "n.vodafone.ne.jp";
    $ret[11] = "s.vodafone.ne.jp";
    $ret[12] = "q.vodafone.ne.jp";
    
    return $ret;
  }

}

?>
