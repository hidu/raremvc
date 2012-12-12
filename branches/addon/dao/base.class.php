<?php
/**
 * rare.hongtao3.com
 * 
 * simple dao 
 * @author duwei
 * @since 2012-04-02
 *@package addon\dao
 */
abstract class rDao_base{
  protected $table_name;
  protected $key_field=null;
  protected $dbConfigName=null;
  
  public function __construct(){
     $this->init();
     if(empty($this->table_name))throw new Exception('table_name is null!');
  }
  protected  abstract function init();
  
  public function getTableName(){
     return $this->table_name;
  }
  
  private function _checkKeyField(){
    if(is_null($this->key_field))throw new Exception('no key field!');
  }
  
  protected function onUpdate(&$data,&$where,&$params){
    
  }
  
  protected function onInsert(&$data){
  
  }
  
  protected function onDelete(&$where,&$params){
  
  }
  
  public function getOneByKey($key){
     $this->_checkKeyField();
    return $this->getOneByField($this->key_field, $key);
  }
  
  public function getOneByField($fieldName,$fieldValue){
    return $this->query("where ".$fieldName."=?",$fieldValue);
  }
   
   public function update($data,$where,$params){
      if(false === $this->onUpdate($data, $where, $params))return false;
      return rDB::table_update($this->table_name, $data, $where,$params,$this->dbConfigName);
   }
   
   public function updateByKey($key,$data){
      $this->_checkKeyField();
      return $this->updateByField($this->key_field, $key, $data);
   }
   
   public function updateByField($fieldName,$fieldValue,$data){
      return $this->update($data, $fieldName."=?", $fieldValue);
   }
   
   public function delete($where,$params){
     if(false === $this->onDelete($where,$params))return false;
     return rDB::table_delete($this->table_name, $where,$params,$this->dbConfigName);
   }
   
   public function deleteByKey($keyValue){
     $this->_checkKeyField();
     return $this->deleteByField($this->key_field, $keyValue);
   }

   public function deleteByField($fieldName,$fieldValue){
     return $this->delete($fieldName."=?", $fieldValue);
   }
   
   public function insert($data){
      if(false === $this->onInsert($data))return false;
      return rDB::table_insert($this->table_name, $data,$this->dbConfigName);
   }
   
   /**
    * 保存数据|新插入或者更新
    * @param array $data
    * @return array | false
    */
   public function save($data){
      $this->_checkKeyField();
      $key=isset($data[$this->key_field])?$data[$this->key_field]:null;
      if(!empty($key)){
         $rt=$this->updateByField($this->key_field, $key, $data);
         if(false !== $rt){
            return $this->getOneByKey($key);
          }
          return false;
      }else{
        $lastId=$this->insert($data);
        if($lastId){
           return $this->getOneByKey($lastId);
        }
      }
      return false;
   }
   
   /**
    * @param string $where
    * @param array $params
    * @param string $fields
    * @return array  一条结果
    */
   public function query($where,$params=null,$fields="*"){
     $sql="select {$fields} from {$this->table_name} ".$where;
     return rDB::query($sql,$params,$this->dbConfigName);
   }
   
   public function getOneField($fieldName,$where,$params=null){
      $one=$this->query($where,$params,$fieldName);
      return isset($one[$fieldName])?$one[$fieldName]:null;
   }
   
   public function queryAll($where='',$params=null,$fields="*"){
     $sql="select {$fields} from {$this->table_name} ".$where;
     return rDB::queryAll($sql,$params,$this->dbConfigName);
   }
   
   public function queryAllBySql($sql,$params=null){
       return rDB::queryAll($sql,$params,$this->dbConfigName);
   }
   
   /**
    * @param string $keyField
    * @param srting $valueField
    * @param string $where
    * @param array $params
    * @return array   eg:array(3=>'a',5=>'b')
    */
  public function queryAllPairs($keyField,$valueField,$where='',$params=null){
      $all=$this->queryAll($where,$params,$keyField.",".$valueField);
      $pairs=array();
      foreach ($all as $row){
         $pairs[$row[$keyField]]=$row[$valueField];
       }
      unset($all);
      return $pairs;
   }
   
   /**
    * 分页查询
    * @param string $where
    * @param array $params
    * @param int $pageSize
    * @return array     ($list,$pager) 
    */
   public function getListPage($where="",$params=null,$pageSize=10){
     $sql="select * from {$this->table_name} ".$where;
    return rDB::listPage($sql,$params,$pageSize,$this->dbConfigName);
   }
   
   public function getListPageBySql($sql,$params=null,$pageSize=10){
    return rDB::listPage($sql,$params,$pageSize,$this->dbConfigName);
   }
   
   
   public function count($where=null,$params=null){
     $one=$this->query($where,$params,"count(*) as count");
     return isset($one['count'])?(int)$one['count']:0;
   }
   
   public function max($fieldName=null,$where=null,$params=null){
     if(empty($fieldName))$fieldName=$this->key_field;
     $one=$this->query($where,$params,"max({$fieldName}) as max");
     return isset($one['max'])?$one['max']:null;
   }
   
   public function min($fieldName=null,$where=null,$params=null){
     if(empty($fieldName))$fieldName=$this->key_field;
     $one=$this->query($where,$params,"min({$fieldName}) as min");
     return isset($one['min'])?$one['min']:null;
   }
   public function avg($fieldName=null,$where=null,$params=null){
     if(empty($fieldName))$fieldName=$this->key_field;
     $one=$this->query($where,$params,"avg({$fieldName}) as avg");
     return isset($one['avg'])?$one['avg']:null;
   }

   public function getTableFields(){
       return rDB::getTableFileds($this->table_name,$this->dbConfigName);
   }
}