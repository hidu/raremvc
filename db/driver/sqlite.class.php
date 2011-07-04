<?php
class rdb_driver_sqlite{
       public static function listPage($sql,$params=array(),$size=10,$dbName=null,$page=1){
        $start= $size*($page-1);
        $limit= $start.','.$size;
        $sqlLimit=$sql." LIMIT ".$limit;
        $list=rDB::queryAll($sqlLimit, $params,$dbName);
        $sql2=preg_replace("/select*\s+from/i", "SELECT count(*) FROM ", $sql);
        $total=(int)rDB::execQuery($sql2,array(),$dbName)->fetchColumn();
        return array($list,$total);
   } 
}