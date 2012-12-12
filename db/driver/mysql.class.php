<?php
/**
 * @author duwei
 *@package addon\db\driver
 */
class rdb_driver_mysql{
   public static function setEncode($encode,$pdo){
     $pdo->exec("SET NAMES $encode");
   }
  
  /**
   * 
   * @param String $sql
   * @param array $params
   * @param int $size
   * @param int $page
   * @param PDO $pdo
   */
   public static function listPage($sql,$params=array(),$size=10,$page=1,$pdo=null){
        $start= $size*($page-1);
        $limit= $start.','.$size;
        $sql=preg_replace("/^select/i", "SELECT SQL_CALC_FOUND_ROWS ", $sql)." LIMIT ".$limit;
        $list=rDB::queryAll($sql,$params,$pdo);
        $sql2='SELECT FOUND_ROWS()';
        $total=(int)$pdo->query($sql2)->fetchColumn();
        return array($list,$total);
   } 
   
   public static function getAllTables($pdo){
       $all=$pdo->query("show tables")->fetchAll();
       $tables=array();
       foreach ($all as $row){
           $tmp=array_values($row);
           $tables[]=$tmp[0];
        }
       return $tables;
   }
   
   public static function getTableDesc($tableName,$pdo){
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