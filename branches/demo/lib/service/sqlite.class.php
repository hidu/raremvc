<?php
class service_sqlite{
  public static  function init(){
    try{
    $q=rDB::exec("select * from article limit 1");
    }catch(Exception $e){
      $sql="create table article(articleid INTEGER PRIMARY KEY AUTOINCREMENT,title varchar(255),body text,ctime INTEGER,mtime INTEGER,stateid INTEGER,cateid INTEGER);";
      rDB::exec($sql);
      $sql="create table category(cateid INTEGER PRIMARY KEY AUTOINCREMENT,catename varchar(255));";
      rDB::exec($sql);
    }
  }
}