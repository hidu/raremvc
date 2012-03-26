<?php
//路由配置文件
$router=array();
$router['index/index'][]=array('url'=>'{catepinyin}','param'=>array('catepinyin'=>'[a-zA-Z0-1]+'),"fn"=>'myRouter::catePinyin');

$router['index/view'][]=array('url'=>"{articleid}-{pinyin}",
                                "param"=>array('articleid'=>"\d+"), 
                                     );
$router['index/view'][]=array('url'=>'{articleid}','param'=>array('articleid'=>'\d+'));

return $router;

