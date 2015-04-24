<?php
class controller extends appController {
  public function logic() {
    $api = $this->param["ARGS"]["api"];
    d("method_exists",method_exists($this,$api));
    if (method_exists($this,$api) === TRUE) {
      $this->$api($this->param["ARGS"])->render("jsonTemplate");
    } else {
      $this->test();
      $this->render();
    }
  }
  
  private function test() {
    //表示
    $this->dispdt["body_area"]["test"] = "Hello World!!";

    //セッション
    $ss = $this->ss->getVar("ss");
    d("index.logic #",print_r($ss,true));
    if (isset($ss) == false || $ss === NULL) {
     $ss = 1;
    } else {
     $ss++;
    }
    $this->ss->setVar("ss",$ss);
    $this->dispdt["body_area"]["session"] = $ss;
    if ($ss >= 3) {
     $this->ss->unsetVar("ss");
    }

    //データベース
    $this->getSingleResult("select sysdate() as dt;", $result);
    $this->dispdt["body_area"]["dt"] = $result["dt"];

    //ログ出力
    d("index.logic #","ログ出力 ".print_r($result,true));
  }
}
?>