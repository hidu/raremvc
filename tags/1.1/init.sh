#!/bin/sh
##当前脚本的用途是初始化一个app。
## 请不要在当前脚本的目录下运行该脚本
##先建立一个app目录，然后到空app目录中运行。重复运行，会将已有的文件覆盖掉
echo "============================================"
echo "=====当前脚本仅仅用来初始化一个APP========="
echo "=====重复运行会覆盖当前已有文件=============="
echo "============================================"

mkdir web/
mkdir layout/ -p
mkdir component/
mkdir module/index/action/ -p
mkdir module/index/view/ -p
mkdir lib
mkdir lib/helper
mkdir config

### index.php file
echo "<?php
include '../`dirname $0`/rareMVC.class.php';
rareContext::createApp()->run();">web/index.php


### index action
echo "<?php
class indexAction extends rareAction{
    public function preExecute(){
    
    }
    public function execute(){
    
    }
    public function executePost(){
    
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
   public function doFilter(){
       session_start();
   }
}
">lib/myFilter.class.php

### default layout
echo '<!DOCTYPE html>
<html>
<head>
<?php rareView::include_title();?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php rareView::include_js_css()?>
</head>
<body>
<?php echo $body;?>
</body>
</html>'>layout/default.php


###default config
echo "<?php
//rare 程序默认的配置文件
\$config=array();
//\$config['no_script_name']=true;                                    // 是否启用url地址重写，隐藏入口文件，默认为true
//\$config['class_autoload']=true;                                      //是否允许类自动装载，默认是允许 app 的 lib 目录下的.class.php 的文件可以
//\$config['class_autoload_option']['dirs']='';                      //定义那个目录下的php 类文件可以允许自动装载，一般情况下不需要添加，不能为空
//\$config['class_autoload_option']['suffix']='.class.php';    //需要类自动装载的php类文件的后缀 
//\$config['suffix']='html';                                                //默认的url后缀
//\$config['timezone']='Asia/Shanghai';                           //默认的时区      
//\$config['charset']='utf-8';                                           //默认的字符编码
//\$config['title']='rare';                                                  //默认的title

return \$config;
">config/default.php

###db config
echo "<?php
//数据库配置文件
\$db=array();

return \$db;
">config/db.php


###.htaccess
echo "<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule (.*)$ index.php/$1 [L]
</IfModule>">web/.htaccess
