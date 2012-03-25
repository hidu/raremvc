<?php
class dao_category extends dao_base{
  public function __construct(){
     $this->tableName="category";
     $this->keyField="cateid";
  }
  public function add($data){
    if(empty($data)||empty($data['catename'])){
      throw new Exception("empty data");
     }
    return  parent::add($data);
  }
}