<?php
/**
 * データベースクラス
 *
 * @version   $Id: db.php,v 3.0
 * @access    public
 */
class db extends putlog {

  private $Connection;
  private $_Server;//接続先のサーバー
  private $_DbName;//データベース名
  private $_User;//ユーザー名
  private $_Password;//パスワード
  private $autocommit = false;
  public $row_count;//参照または更新された件数

  public function get_Server() {
    return $this->_Server;
  }

  /**
   *  DBに接続する
   *
   * @since     1.0
   * @param     string    $Server   サーバー名
   * @param     string    $DbName   データベース名
   * @param     string    $User   ユーザー名
   * @param     string    $Password   パスワード
   * @access    public
   * @return    boolean   成功時true
   */
  public function connect( $Server='localhost', $DbName='test', $User='root', $Password='' ) {
    try {
      $this->_Server = $Server;
      $this->_DbName = $DbName;
      $this->_User = $User;
      $this->_Password = $Password;
      $this->Connection = @mysqli_connect( $Server, $User, $Password );
      if ($this->Connection == false) {
        $this->error("connect","Server=>".$this->_Server);
        $this->error("connect",mysqli_errno().":".mysqli_error());
        return false;
      }
      return mysqli_select_db( $this->Connection, $DbName );
    } catch (Exception $e) {
      $this->error("connect","Server=>".$this->_Server);
      $this->error("connect",$e->getMessage());
      return false;
    }
  }

  /**
   *  接続解除
   *
   * @since     1.0
   * @param
   * @access    public
   * @return    boolean   成功時true
   */
  public function close() {
    try {
      return mysqli_close( $this->Connection );
    } catch (Exception $e) {
      $this->error("close",$e->getMessage());
      return false;
    }
  }

  /**
   *  クエリー実行
   *
   * @since     1.0
   * @param     string    $SqlQuery   実行系クエリー
   * @access    public
   * @return    void
   */
  public function Query( $SqlQuery ) {
    try {
     if (SQL_LOG == 1) {
      d("Query","===debug backtrace===");
      $trace = debug_backtrace();
      if ( is_array( $trace[0] ) ) d("debug backtrace 0",$trace[0]["class"].".".$trace[0]["function"].":".$trace[0]["line"]);
      if ( is_array( $trace[1] ) ) d("debug backtrace 1",$trace[1]["class"].".".$trace[1]["function"].":".$trace[1]["line"]);
      if ( is_array( $trace[2] ) ) d("debug backtrace 2",$trace[2]["class"].".".$trace[2]["function"].":".$trace[2]["line"]);
      if ( is_array( $trace[3] ) ) d("debug backtrace 3",$trace[3]["class"].".".$trace[3]["function"].":".$trace[3]["line"]);

      d("Query","SqlQuery=>".$SqlQuery);
     }
     $ret = @mysqli_query( $this->Connection,$SqlQuery );
     $this->row_count = @mysqli_affected_rows($this->Connection);
     if ( mysqli_errno($this->Connection) ) {
       $this->error("Query","Server=>".$this->_Server);
       $this->error("Query",mysqli_errno($this->Connection).":".mysqli_error($this->Connection).":SqlQuery=>".$SqlQuery);
       return false;
     }
     if (SQL_LOG == 1) {
      d("Query","row_count=>".print_r($this->row_count,true));
     }
     return $ret;
    } catch (Exception $e) {
      $this->error("Query","Server=>".$this->_Server);
      $this->error("Query",$e->getMessage().":SqlQuery=>".$SqlQuery);
      return false;
    }
  }

  /**
   *  フェッチ
   *
   * @since     1.0
   * @param     array    $Result   クエリー結果
   * @access    public
   * @return    void
   */
  private function Fetch( $Result ) {
    return mysqli_fetch_array( $Result );
  }

  /**
   *  クエリーとフェッチ
   *
   * @since     1.0
   * @param     string    $SqlQuery   参照系クエリー
   * @access    public
   * @return    void
   */
  public function QueryEx( $SqlQuery='' ) {
    try {
      if ($SqlQuery == "") return false;

      $Result = $this->Query( $SqlQuery );
      $this->row_count = @mysqli_num_rows($Result);
      if ( !$Result ) {
        return false;
      }
      $tmp= array();
      while ($row = mysqli_fetch_array($Result,MYSQL_ASSOC)) {
        array_push( $tmp , $row );
      }

      if (SQL_LOG == 1) {
       d("QueryEx","Result=>".print_r($tmp,true));
      }
      return $tmp;
    } catch (Exception $e) {
      $this->error("Query","Server=>".$this->_Server);
      $this->error("QueryEx",$e->getMessage());
      return false;
    }
  }

  public function set_charset($charset) {
    try {
      if (version_compare(PHP_VERSION, '5.0.5') === 1) {
        mysqli_set_charset($this->Connection,$charset);//PHP5 MySQL 5.0.5 以降から有効
      } else {
        mysql_query("SET NAMES '".$charset."'");
      }
    } catch (Exception $e) {
      $this->error("set_charset","Server=>".$this->_Server,__FILE__.":".__LINE__." ".__METHOD__);
      $this->error("set_charset",$e->getMessage(),__FILE__.":".__LINE__." ".__METHOD__);
      return false;
    }
  }

  public function set_autocommit($auto=true) {
    if ($auto == true) {
      $this->autocommit = true;
      mysql_query("SET AUTOCOMMIT=1;");//有効
    } else {
      $this->autocommit = false;
      mysql_query("SET AUTOCOMMIT=0;");//無効
    }

  }

  public function beginTransaction() {
    if ($this->autocommit == true) return;
    mysql_query("START TRANSACTION;");
  }

  public function commit() {
    if ($this->autocommit == true) return;
    mysql_query("COMMIT;");
  }

  public function rollback() {
    if ($this->autocommit == true) return;
    mysql_query("ROLLBACK;");
  }

# **********************************************************
# バージョン文字列取得
# **********************************************************
  function Version() {
    $Field = $this->QueryEx( "show variables like 'version'" );
    return $Field[1];
  }

  /**
   *  複数行を登録するinsert文を生成して返す
   *
   * @since     1.0
   * @param     string    $sql   insert文の頭
   * @param     array    $data   insert文のデータ
   * @access    public
   * @return    void
   */
  function insert($sql,$data) {
   $tmp = "";
   $hash = array();
   if ( is_array( $data ) && (sizeof($data) > 0) ) {
    foreach( $data as $key=>$val ) {
     if ($key == 0) {
      $hash = array_keys($val);
     }

     if ( is_array( $hash ) && (sizeof($hash) > 0) ) {
      $row = "";
      foreach( $hash as $col ) {
       $row .= ",'".$val[$col]."'";
      }
      $row = substr($row,1);
     }
     $tmp .= ",(".$row.")";


    }
    $tmp = substr($tmp,1);
    $sql = $sql.$tmp;
   }
   return $sql;
  }

  /**
   *  指定したテーブルのフィールドを返す
   *
   * @since     1.0
   * @param     string    $table_name   テーブル名
   * @access    public
   * @return    array
   */
  public function fields($table_name) {
      try {
       $connectDB = mysql_connect( $this->_Server, $this->_User, $this->_Password );
       mysql_select_db ( $this->_DbName, $connectDB );

       $sql = "";
       $sql = "SELECT * FROM {$table_name}";
       $res = mysql_query($sql,$connectDB);
       //$res = mysqli_query( $this->Connection,$sql );
       //フィールドの数を取得する
       $numFields = mysql_num_fields($res);

       $nameFields = array();
       for($i=0;$i<$numFields;$i++){
        $nameFields[] = mysql_field_name($res, $i);
       }
       mysql_close($connectDB);
       return $nameFields;
    } catch (Exception $e) {
      $this->error("fields","Server=>".$this->_Server,__FILE__.":".__LINE__." ".__METHOD__);
      $this->error("fields",$e->getMessage(),__FILE__.":".__LINE__." ".__METHOD__);
      return false;
    }
  }

  public function fieldType($table_name,$field) {
   try {
    $sql = <<<EOT
      SHOW COLUMNS FROM {$table_name} where Field = '{$field}'
EOT;
    $data = $this->QueryEx($sql);

    if ( is_array( $data ) && (sizeof($data) > 0) ) {
     return $data[0];
    } else {
     return false;
    }
   } catch (Exception $e) {
    $this->putlog->error("db.fieldType",$e->getMessage(),__FILE__.":".__LINE__." ".__METHOD__);
    return false;
   }
  }

}
?>
