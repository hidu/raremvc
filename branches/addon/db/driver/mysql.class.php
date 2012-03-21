<?php
class rdb_driver_mysql{
   public static function listPage($sql,$params=array(),$size=10,$dbName=null,$page=1){
        $start= $size*($page-1);
        $limit= $start.','.$size;
        $sql=preg_replace("/^select/i", "SELECT SQL_CALC_FOUND_ROWS ", $sql)." LIMIT ".$limit;
        $list=rDB::queryAll($sql, $params,$dbName);
        $sql2='SELECT FOUND_ROWS()';
        $total=(int)rDB::execQuery($sql2,array(),$dbName)->fetchColumn();
        return array($list,$total);
   } 
   
   public static function getAllTables($dbName=null,$type='slave'){
       $pdo=rDB::getPdo($dbName,$type);
       return $pdo->query("show tables")->fetchAll();
   }
   
   public static function getTableDesc($tableName,$dbName=null,$type='slave'){
      $pdo=self::getPdo($dbName,'slave');
      $result=$pdo->query("desc $tableName")->fetchAll();
      $desc=array();
      foreach ($result as $field){
          $desc[$field['Field']]=array('name'=>$field['Field'],
                                        'type'=>$field['Type'],
                                        'default'=>$field['Default']);
       }
      return $desc;
   }
}