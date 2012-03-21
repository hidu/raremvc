<?php
//路由配置文件
$router=array();
$router['index/view'][]=array('url'=>'{articleid}','param'=>array('articleid'=>'\d+'));
return $router;

