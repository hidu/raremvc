<?php
$db=array();
$db['dsn']="sqlite:".dirname(dirname(__FILE__))."/data/blog.sqlite";
return $db;