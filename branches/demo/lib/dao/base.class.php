<?php
abstract class dao_base{
   protected $tableName=null;
   
   public function getOneByField($fieldName,$fieldValue){
      $sql="select * from {$this->tableName} where {$fieldName}=?";
      return rDB::query($sql,$fieldValue);
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
}