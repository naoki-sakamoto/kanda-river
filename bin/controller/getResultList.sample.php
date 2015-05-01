<?php
class controller extends appController {
  public function logic() {
    $api = $this->param["ARGS"]["api"];
    d("method_exists",method_exists($this,$api));
    if (method_exists($this,$api) === TRUE) {
      $this->$api($this->param["ARGS"])->render("jsonTemplate");
    } else {
      $this->sample();
      $this->render();
    }
  }
  
  private function sample() {
    $this->getResultList("select * from sample", $data);
    $this->dispdt["body_area"]["sample_area"] = $data;
   d("getResultList.sample.sample #",print_r($data,true));
    
    
  }
}
?>