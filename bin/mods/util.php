<?php
class util {
 private $db_master;
 private $db_slave;
 public function set_db($_db_master,$_db_slave) {
  $this->db_master = $_db_master;
  $this->db_slave = $_db_slave;
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
   $this->putlog->error("util.callBySql",$e->getMessage(),__FILE__.":".__LINE__." ".__METHOD__);
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
   $this->putlog->error("util.getResultList",$e->getMessage(),__FILE__.":".__LINE__." ".__METHOD__);
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
   $this->putlog->error("util.getSingleResult",$e->getMessage(),__FILE__.":".__LINE__." ".__METHOD__);
   return $this;
  }
 }

}
?>