#!/bin/sh
##当前脚本的用途是初始化一个app。
## 请不要在当前脚本的目录下运行该脚本
##先建立一个app目录，然后到空app目录中运行。重复运行，会将已有的文件覆盖掉
echo "============================================"
echo "=====当前脚本仅仅用来初始化一个APP========="
echo "=====重复运行会覆盖当前已有文件=============="
echo "============================================"
mkdir cache/
chmod 777 cache
mkdir lib/
mkdir front/
cd front/

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
include '`dirname $0`/rareMVC.class.php';
rareContext::createApp()->run();">web/index.php


### index action
echo "<?php
class indexAction extends rareAction{
    public function preExecute(){
    
    }
    public function execute(){
    
    }
    public function executeGet(){
    
    }
    public function executePost(){
    
    }
}
">module/index/action/index.php

### index view
echo "
<?php slot_start('slot1'); ?>
恩 不错，这个会显示在左侧！
<?php slot_end();?>
你好，现在的是：<?php echo date('Y-m-d H:i:s'); ?>
">module/index/view/index.php


### demo action
echo "<?php
//注意 该action的类名，使用的是包含 moduleName的全称
class indexDemoAction extends rareAction{
    //本action只响应 get请求，其他方式的请求 如POST 会抛出404
    public function executeGet(){
    
    }
}
">module/index/action/demo.php

###default filter
echo "<?php
/**
 *app default filter 
 */
class myFilter{
    
    //只会运行一次
   public function doFilter(){
       session_start();
   }
   public function beforeExecute(){
      //任何action之前前都会运行，forward也生效
   }
}
">lib/myFilter.class.php

### default layout
echo "<!DOCTYPE html>
<html>
<head>
<?php rareView::include_title();?>
<?php rareView::include_js_css()?>
</head>
<body>
<?php echo fetch('component1','time='.date('H:i:s'));?>
<div style='width:180px;float:left;border:1px solid blue;min-height:400px'><?php echo slot_get('slot1')?></div>
<div style='margin-left:190px;min-height:400px;border:1px solid blue;'><?php echo \$body;?></div>
</body>
</html>">layout/default.php


###default config
echo "<?php
//rare 程序默认的配置文件
\$config=array();
//\$config['no_script_name']=true;                                   // 是否启用url地址重写，隐藏入口文件，默认为true
//\$config['class_autoload']=true;                                   //是否允许类自动装载，默认是允许 app 的 lib 目录下的.class.php 的文件可以
//\$config['class_autoload_option']['dirs']='';                      //定义那个目录下的php 类文件可以允许自动装载，一般情况下不需要添加，不能为空
//\$config['class_autoload_option']['suffix']='.class.php';          //需要类自动装载的php类文件的后缀 
//\$config['suffix']='html';                                         //默认的url后缀
//\$config['timezone']='Asia/Shanghai';                              //默认的时区      
//\$config['charset']='utf-8';                                       //默认的字符编码
//\$config['title']='rare';                                          //默认的title
//\$config['meta.keywords']='';                                      //keywords
//\$config['meta.description']='';                                   //description
//可以继续定义其他任何meta.xxxxx
//\$config['rest']=false;                                            //是否启用action的自定义rest

return \$config;
">config/default.php

###db config
echo "<?php
//数据库配置文件
\$db=array();

return \$db;
">config/db.php

###router config
echo "<?php
//数据库配置文件
\$router=array();

return \$router;
">config/router.php


###.htaccess
echo "<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule (.*)$ index.php/$1 [L]
</IfModule>">web/.htaccess

###component
echo "
<div style='float:right'>当前时间：<?php echo \$time;?></div>
<h1><a href='<?php echo url('index') ?>'>rare demo</a></h1>
">component/component1.php

