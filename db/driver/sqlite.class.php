<?php
class rdb_driver_sqlite{
     public static function setEncode($encode,$pdo){
        $pdo->exec("PRAGMA encoding = '$encode';");
     }
  
       public static function listPage($sql,$params=array(),$size=10,$page=1,$pdo=null){
        $start= $size*($page-1);
        $limit= $start.','.$size;
        $sqlLimit=$sql." LIMIT ".$limit;
        $list=rDB::queryAll($sqlLimit,$params,$pdo);
        $sql2=preg_replace("#^select\s+.*\sfrom\s#i", "SELECT count(*) FROM ", $sql);
        $total=(int)$pdo->query($sql2)->fetchColumn();
        return array($list,$total);
   }
    
    public static function getAllTables($pdo){
       $all= $pdo->query("select name from sqlite_master where type='table'")->fetchAll();
       $tables=array();
       foreach ($all as $row){
         $tables[]=$row['name'];
        }
       return $tables;
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
   public static function getTableDesc($tableName,$pdo){
      $result= $pdo->query("PRAGMA table_info($tableName)")->fetchAll();
      $desc=array();
      foreach ($result as $field){
        $desc[$field['name']]=array('name'=>$field['name'],'type'=>$field['type'],'default'=>$field['dflt_value']);
       }
      return $desc;
   }
}