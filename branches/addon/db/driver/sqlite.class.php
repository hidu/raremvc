<?php
class rdb_driver_sqlite{
       public static function listPage($sql,$params=array(),$size=10,$dbName=null,$page=1){
        $start= $size*($page-1);
        $limit= $start.','.$size;
        $sqlLimit=$sql." LIMIT ".$limit;
        $list=rDB::queryAll($sqlLimit, $params,$dbName);
        $sql2=preg_replace("#^select\s+.*\sfrom\s#i", "SELECT count(*) FROM ", $sql);
        $total=(int)rDB::execQuery($sql2,$params,$dbName)->fetchColumn();
        return array($list,$total);
   }
    
    public static function getAllTables($dbName=null,$type='slave'){
       $pdo=rDB::getPdo($dbName,$type);
       return $pdo->query("select name from sqlite_master where type='table'")->fetchAll();
   }
   
   /**
    * 
    |    |    [  cid  ] = String(1) "0"
    |    |    [  name  ] = String(2) "id"
    |    |    [  type  ] = String(3) "int"
    |    |    [  notnull  ] = String(1) "0"
    |    |    [  dflt_value  ] = NULL(0) NULL
    |    |    [  pk  ] = String(1) "0"
    * 
    * @param string $tableName
    * @param string $dbName
    * @param string $type
    */
   public static function getTableDesc($tableName,$dbName=null,$type='slave'){
      $pdo=rDB::getPdo($dbName,$type);
      $result= $pdo->query("PRAGMA table_info($tableName)")->fetchAll();
      $desc=array();
      foreach ($result as $field){
        $desc[$field['name']]=array('name'=>$field['name'],'type'=>$field['type'],'default'=>$field['dflt_value']);
       }
      return $desc;
   }
}