<?php
/**
 * HTML_Template_Sigmaのインターフェースを連想配列にする。
 *
 * @version   $Id: tau.php,v 3.0
 * @access    public
 * ギリシア文字でsigma[シグマ]の次の文字tau[タウ]
 */
class tau {
  /**
   *  テンプレート　オブジェクト
   */
  var $tpl;
  var $CurrentBlock="";//カレントブロック


  /**
   *  テキストファイルを入力して
   *  HTML_Template_Sigmaのオブジェクトを生成する
   *
   * @since     1.0
   * @param     string    $tpl_name   テンプレート(ファイル名)
   * @param     string    $tpl_dir    ファイルディレクトリ
   * @access    public
   * @return    void
   */
  public function loadTemplatefile($tpl_name, $tpl_dir = ".") {
    $tpl = new HTML_Template_Sigma($tpl_dir);
    $tpl->loadTemplatefile($tpl_name,true,true);

    $this->tpl = &$tpl;
  }

  /**
   *  テキストを入力して
   *  HTML_Template_Sigmaのオブジェクトを生成する
   *
   * @since     1.0
   * @param     string    $tpl_txt   テンプレート(テキスト)
   * @access    public
   * @return    void
   */
  public function setTemplate($tpl_txt) {
    $tpl = new HTML_Template_Sigma(".");
    $tpl->setTemplate( $tpl_txt, true, true);
    $this->tpl = &$tpl;
  }

  /**
   *  連想配列を渡して変数の値を設定する
   *
   * @since     1.0
   * @param     string    $data   連想配列
   *                              $key:変数名
   *                              $val:値
   * @access    public
   * @return    void
   */
  public function array_func($data) {
    if ( is_array( $data ) && (sizeof($data) > 0) ) {
      foreach( $data as $key=>$val ) {
        $this->setVariable($val, $key);
      }
    }
  }

  //通常
  private function setVariable($item, $key) {
    if ( is_array( $item ) && (sizeof($item) > 0) ) {
      $this->setBlock($key, $item);
    } else if (is_bool($item)) {
      if ($item === true)
        $this->tpl->touchBlock( $key );
      else if ($item === false)
        $this->tpl->hideBlock( $key );
    } else {
      $this->tpl->setVariable( $key , $item );

    }
  }

  //ブロック
  private function setBlock($block, &$data) {
    $is_list = false;//リスト形式か？
    if ( is_array( $data ) && (sizeof($data) > 0) ) {
      if (!is_numeric($block)) {
        if ($this->tpl->blockExists($block) == false) return false;
        $this->tpl->setCurrentBlock( $block );
        $this->CurrentBlock = $block;
      } else {
        $is_list = true;
      }
      $tmp = array();
      foreach( $data as $key=>$val ) {
        if ( is_array( $val ) && (sizeof($val) > 0) ) {
          $this->setBlock($key, $val);
        } else {
          $tmp[$key] = $val;
        }
      }

      if (sizeof($tmp) > 0) {
        $this->tpl->setVariable( $tmp );
      }

      if (!is_numeric($block)) {
        $this->tpl->parse($block);
      } else {
        $this->tpl->parse($this->CurrentBlock);
      }
    }
  }

  /**
   *  国際化対応
   *
   * @since     1.0
   * @param     array    $dispdt   フロントオブジェクト
   * @param     array    $data     英語、日本語の辞書
   * @param     string  $lang  言語
   * @access    public
   * @return    void
   */
  public function merge($dispdt,$data,$lang) {
    $tplArray = $this->templateBlockArray("",$data,$lang);//HTMLテンプレートから変数を探して、$data内に存在すれば値をセットする。
    //var_dump($tplArray);
    $result = array_merge_recursive($dispdt, $tplArray);//配列をマージ
    return $result;
  }

  /**
   *  テンプレート内の変数を配列にして返す
   *
   * @since     1.0
   * @param     string  $block ブロック
   * @param     array   $data  英語、日本語の辞書
   * @param     string  $lang  言語
   * @access    private
   * @return    array
   */
  private function templateBlockArray($block = "",$data,$lang) {
    $result = array();
    if ($block == "") {
      $blockList = $this->tpl->getBlockList();
    } else {
      $blockList = $this->tpl->getBlockList($block);
    }

    if ( is_array( $blockList ) && (sizeof($blockList) > 0) ) {
      foreach( $blockList as $key=>$val ) {
        $result[$val] = $this->templateBlockArray($val,$data,$lang);
        $tmp = $this->tpl->getPlaceholderList($val);
        if ( is_array( $tmp ) && (sizeof($tmp) > 0) ) {
          foreach( $tmp as $key2=>$val2 ) {
            if (isset($data[$val2]) == true) {
              $result[$val][$val2] = $data[$val2][$lang];
            }

          }
        }
      }
    }
    return $result;
  }


}
?>