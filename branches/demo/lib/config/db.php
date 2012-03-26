<?php
define('DB_TYPE', 'sqlite');


$db=array();
if(DB_TYPE=="sqlite"){
  
   $db['dsn']="sqlite:".dirname(dirname(__FILE__))."/data/blog.sqlite";

}else {
  
  //若使用mysql数据库请修改下列数据库配置
  $db['dsn']="mysql:host=127.0.0.1; port=3306; dbname=rare_demo";
  $db['username']='xxx';
  $db['passwd']='xxx';

}
return $db;