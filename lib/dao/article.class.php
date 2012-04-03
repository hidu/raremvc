<?php
class dao_article extends rDao_base{
  
  public function init(){
     $this->table_name="article";
     $this->key_field="articleid";
  }
  
  public function onUpdate($data, $where, $params){
    $data['mtime']=time();
  }
  
 public function onInsert($data){
     $data['ctime']=$data['mtime']=time();
 }
}