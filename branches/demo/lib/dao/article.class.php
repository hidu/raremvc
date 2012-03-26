<?php
class dao_article extends dao_base{
  public function __construct(){
     $this->tableName="article";
     $this->keyField="articleid";
  }
  
  public function updateByField($fieldName, $fieldValue, $data){
    $data['mtime']=time();
    return parent::updateByField($fieldName, $fieldValue, $data);
  }
  
  public function add($data){
     $data['ctime']=$data['mtime']=time();
     return parent::add($data);
  }
  public function save($data){
    if(empty($data[$this->keyField])){
       $data['ctime']=$data['mtime']=time();
       unset($data[$this->keyField]);
    }
    return parent::save($data);
  }
}