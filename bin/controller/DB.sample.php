<?php
class controller extends appController {
  public function logic() {
    if ($this->param["POST"]["btn"] == "insert") {
      $this->insert($this->param["POST"]["name"]);
      $this->select();
    } else if ($this->param["POST"]["btn"] == "update") {
      $this->update($this->param["POST"]["no"],$this->param["POST"]["name"]);
      $this->select();
    } else if ($this->param["POST"]["btn"] == "delete") {
      $this->delete($this->param["POST"]["no"]);
      $this->select();
    } else if ($this->param["POST"]["btn"] == "select") {
      $this->select($this->param["POST"]["no"]);
    } else {
      $this->select();
    }
    $this->render();
  }

  /**
   *  複数検索
   *
   * @since     1.0
   * @param
   * @access    private
   * @author naoki.sakamoto
   * @return    void
   */
  private function select($no=NULL) {
    d("select","no=>".$no);
    if ($no == NULL) {
      d("select","複数検索");
      $this->getResultList("select * from sample", $data);//複数検索
      $this->dispdt["body_area"]["sample_area"] = $data;//テンプレートエンジンに代入
    } else {
      d("select","単一検索");
      $this->getSingleResult("select * from sample where no = {$no}", $data);//単一検索
      $this->dispdt["body_area"]["sample_area"] = $data;//テンプレートエンジンに代入
    }
    d("select",print_r($data,true));//ログ出力
  }

  /**
   *  登録
   *
   * @since     1.0
   * @param     string    $name   名前
   * @access    private
   * @author naoki.sakamoto
   * @return    void
   */
  private function insert($name) {
    $name = q($name);
    $this->callBySql("insert into sample (name) values ('{$name}');");//insert実行
  }

  /**
   *  変更
   *
   * @since     1.0
   * @param     string    $no   No
   * @param     string    $name   名前
   * @access    private
   * @author naoki.sakamoto
   * @return    void
   */
  private function update($no,$name) {
    $no = q($no);
    $name = q($name);
    $this->callBySql("update sample set name = '{$name}' where no = {$no};");//update実行
  }

  /**
   *  削除
   *
   * @since     1.0
   * @param     string    $no   No
   * @access    private
   * @author naoki.sakamoto
   * @return    void
   */
  private function delete($no) {
    $no = q($no);
    $this->callBySql("delete from sample where no = {$no};");//delete実行
  }

}
?>