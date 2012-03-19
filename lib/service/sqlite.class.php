<?php
class service_sqlite{
  public static  function init(){
    try{
    $q=rDB::exec("select * from article limit 1");
    }catch(Exception $e){
      $sql="create table article(articleid int,title varchar(255),body text,ctime int,mtime int,stateid int,cateid int);CREATE UNIQUE INDEX [cache_unique] ON article ([articleid])";
      rDB::exec($sql);
      $sql="create table category(cateid int,catename varchar(255));CREATE UNIQUE INDEX [cache_unique] ON category ([cateid])";
      rDB::exec($sql);
    }
  }
}