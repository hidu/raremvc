<?php
/**
 * @author duwei
 * @package addon\db\driver
 */
class rdb_driver_postgresql{
   public static function setEncode($encode,$pdo){
      $pdo->exec("SET NAMES $encode");
  }
   public static function listPage($sql,$param,$size=10,$page=1,$pdo=null){
        $start= $size*($page-1);
        $sqlLimit=$sql." LIMIT {$size} OFFSET ".$start;
        $list=$pdo->query($sqlLimit)->fetchAll();
        
        $sql2=preg_replace("#^select\s+.*\sfrom\s#i", "SELECT count(*) FROM ", $sql);
        $total=(int)$pdo->query($sql2)->fetchColumn();
        return array($list,$total);
   } 
}