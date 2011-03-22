<?php
class rdb_driver_mysql{
   public static function listPage($sql,$params=array(),$size=10,$dbName=null,$page=1){
        $sql=trim($sql);
        $start= $size*($page-1);
        $limit= $start.','.$size;
        $sql=preg_replace("/^select/i", "SELECT SQL_CALC_FOUND_ROWS ", $sql)." LIMIT ".$limit;
        $list=rDB::queryAll($sql, $params,$dbName);
        $sql2='SELECT FOUND_ROWS()';
        $total=(int)rDB::execQuery($sql2,array(),$dbName)->fetchColumn();
        return array($list,$total);
   } 
}