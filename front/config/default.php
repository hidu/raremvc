<?php
//rare 程序默认的配置文件
$config=array();
//$config['no_script_name']=true;                                   // 是否启用url地址重写，隐藏入口文件，默认为true
//$config['class_autoload']=true;                                   //是否允许类自动装载，默认是允许 app 的 lib 目录下的.class.php 的文件可以
//$config['class_autoload_option']['dirs']='';                      //定义那个目录下的php 类文件可以允许自动装载，一般情况下不需要添加，不能为空
//$config['class_autoload_option']['suffix']='.class.php';          //需要类自动装载的php类文件的后缀 
//$config['suffix']='html';                                         //默认的url后缀
//$config['timezone']='Asia/Shanghai';                              //默认的时区      
//$config['charset']='utf-8';                                       //默认的字符编码
//$config['title']='rare';                                          //默认的title
//$config['meta.keywords']='';                                      //keywords
//$config['meta.description']='';                                   //description
//可以继续定义其他任何meta.xxxxx
//$config['rest']=false;                                            //是否启用action的自定义rest
$config['css']="style.css";
return $config;

