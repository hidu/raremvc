<?php
abstract class dao_base{
   protected $tableName=null;
   protected $keyField=null;
   
   public function getOneByField($fieldName,$fieldValue){
     return $this->query($fieldName."=?",$fieldValue);
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
   
   public function deleteByKey($keyValue){
     if(is_null($this->keyField))throw new Exception('no key field!');
     return $this->deleteByField($this->keyField, $keyValue);
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
   
   public function query($where,$params=null){
     $sql="select * from {$this->tableName}";
     if(!empty($where))$sql.=" where ".$where;
     return rDB::query($sql,$params);
   }
   public function queryAll($where="",$params=null){
     $sql="select * from {$this->tableName}";
     if(!empty($where))$sql.=" where ".$where;
     return rDB::queryAll($sql,$params);
   }
   
   public function queryAllPairs($keyField,$valueField,$where='',$whereParams=array()){
      $all=$this->queryAll($where,$whereParams);
       return qArray::toHashmap($all, $keyField,$valueField);
   }
   
   public function getListPage($where="",$sqlParams=array(),$pageSize=10){
     $sql="select * from {$this->tableName}";
     if(!empty($where))$sql.=" where ".$where;
    return rDB::listPage($sql,$sqlParams,$pageSize);
   }
}