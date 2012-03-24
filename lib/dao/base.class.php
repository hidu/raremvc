<?php
abstract class dao_base{
   protected $tableName=null;
   protected $keyField=null;
   
   public function getOneByField($fieldName,$fieldValue){
      $sql="select * from {$this->tableName} where {$fieldName}=?";
      return rDB::query($sql,$fieldValue);
   }
   
   public function getByKey($keyValue){
     if(is_null($this->keyField))throw new Exception('no key field!');
     return $this->getOneByField($this->keyField, $keyValue);
   }
   
   public function deleteByField($fieldName,$fieldValue){
     return rdb::table_delete($this->tableName, "{$fieldName}=?",$fieldValue);
   }
   
   public function updateByField($fieldName,$fieldValue,$data){
      return rDB::table_update($this->tableName, $data, "{$fieldName}=?",$fieldValue);
   }
   
   public function add($data){
      return rDB::table_insert($this->tableName, $data);
   }
   
   public function save($data){
      if(is_null($this->keyField))throw new Exception('no key field!');
      $key=isset($data[$this->keyField])?$data[$this->keyField]:null;
      if(!empty($key)){
         $rt=$this->updateByField($this->keyField, $key, $data);
         if(false !== $rt){
            return $this->getByKey($key);
         }
      }
      $lastId=$this->add($data);
      if($lastId){
         return $this->getByKey($lastId);
      }
      return false;
   }
   
   public function query($sql,$params=null){
     return rDB::query($sql,$params=null);
   }
   public function queryAll($sql,$params=null){
     return rDB::queryAll($sql,$params=null);
   }
   
   public function getListPage($sqlMore="",$sqlParams=array(),$pageSize=10){
    return rDB::listPage("select * from {$this->tableName} where 1 {$sqlMore}",$sqlParams,$pageSize);
   }
}