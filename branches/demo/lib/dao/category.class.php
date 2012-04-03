<?php
class dao_category extends rDao_base{
  public function init(){
     $this->table_name="category";
     $this->key_field="cateid";
  }
  public function onInsert($data){
    if(empty($data)||empty($data['catename'])){
      throw new Exception("empty data");
     }
  }
}