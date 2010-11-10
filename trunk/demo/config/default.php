<?php
return array(
//   'no_script_name'=>true,               // 是否启用url地址重写，隐藏入口文件，默认为true
//   'class_autoload'=>true,               //是否允许类自动装载，默认是允许 app 的 lib 目录下的.class.php 的文件可以
   'class_autoload_option'=>array(
//                'dirs'=>'',              //定义那个目录下的php 类文件可以允许自动装载，一般情况下不需要添加，不能为空
//                ‘suffix’=>'.class.php'   //需要类自动装载的php类文件的后缀 
                  "hand"=>true,   //是否手动更新class 路径文件 ，为false 时 缓存文件写入到cache 目录中，
                                       //为true 是需要手动允许ararAutoLoad.class.php 文件
                  "rootDir"=>dirname(dirname(dirname(__FILE__))), //hand=true 时 需要定义 程序根目录
                  "noCache"=>true 
                 ),
//    'suffix'=>'html'                     //默认的url后缀
   'title'=>'rare demo app',
   'js'=>'js/jquery.js,js/jqueryform.js',
);