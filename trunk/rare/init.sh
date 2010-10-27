#!/bin/sh
## 请不要在当前目录下允许该脚本
##到空的目录中运行，不要重复允许，会将已有的文件覆盖掉

mkdir web/
mkdir templates/layout/ -p
mkdir templates/component/
mkdir module/index/action/ -p
mkdir module/index/view/ -p
mkdir lib
mkdir config
mkdir cache
chmod 777 cache

### index.php file
echo "<?php
include '.`dirname $0`/rareMVC.class.php';
rareContext::createApp()->run();">web/index.php


### index action
echo "<?php
class indexAction extends rareAction{
    public function execute(){
    
    }
}
">module/index/action/index.php

### index view
echo "<center>hello is me</center>">module/index/view/index.php

###default filter
echo "<?php
/**
 *app default filter 
 */
class myFilter{
   public function __construct(\$context){
   }
}
">lib/myFilter.class.php

### default layout
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>rare app</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<?php echo $content;?>
</body>
</html>'>templates/layout/default.php


###default config
echo "<?php
//程序默认的配置文件
return array(
//   'no_script_name'=>true,               // 是否启用url地址重写，隐藏入口文件，默认为true
//   'class_autoload'=>true,               //是否允许类自动装载，默认是允许 app 的 lib 目录下的.class.php 的文件可以
//   'class_autoload_option'=>array(
//                'dirs'=>'',              //定义那个目录下的php 类文件可以允许自动装载，一般情况下不需要添加，不能为空
//                ‘suffix’=>'.class.php'   //需要类自动装载的php类文件的后缀 
//                 ),
//    'suffix'=>'html'                     //默认的url后缀    
);
">config/default.php

###db config
echo "<?php
//数据库配置文件
return array(

);
">config/db.php


###.htaccess
echo "<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule (.*)$ index.php/$1 [L]
</IfModule>">web/.htaccess