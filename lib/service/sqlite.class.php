<?php
class service_sqlite{
  public static  function init(){
    $tables=array();
    $tables['article']="create table article(articleid INTEGER PRIMARY KEY AUTOINCREMENT,title varchar(255),pinyin varchar(255),body text,ctime INTEGER,mtime INTEGER,stateid INTEGER,cateid INTEGER);";
    $tables['category']="create table category(cateid INTEGER PRIMARY KEY AUTOINCREMENT,catename varchar(255),pinyin varchar(255));";
    foreach ($tables as $tableName=>$sql){
       try{
          $q=rDB::exec("select * from {$tableName} limit 1");
       }catch(Exception $e){
          rDB::exec($sql);
        }
    }

  }
}