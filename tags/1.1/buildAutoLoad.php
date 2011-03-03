<?php
//使用该工具生成autoload配置文件
//请将当前脚本在app的config目录下调用
//cli 模式下 手动扫描lib 目录下的class 文件 参数为app 的路径
//php ../../rare/buildAutoLoad.php
//autoLoad 文件会写入到 ../demo/config/autoLoad.php 文件中

$appDir=dirname(getcwd())."/";
$rootDir=dirname($appDir)."/";

$defaultConfigFile=$appDir."/config/default.php";
$autoLoadFile=$appDir."/config/autoLoad.php";
if(!file_exists($defaultConfigFile)){
  die("默认的配置文件不存在！请确认是在app的config目录下调用当前文件");
}

if(file_exists($autoLoadFile)){
  unlink($autoLoadFile);
  echo "remove old file!\n";
}
$option=array();
if(file_exists($defaultConfigFile)){
  $defaultConfig=require $defaultConfigFile;
  $option=isset($defaultConfig['class_autoload_option'])?$defaultConfig['class_autoload_option']:array();
}
if(empty($option['dirs']))$option['dirs']=$appDir."lib/,".$rootDir."lib/";
$option['cache']=$appDir."/config/autoLoad";
$option['noCache']=false;
$option['hand']=false;
date_default_timezone_set('Asia/Shanghai');
include 'rareMVC.class.php';
include 'rareAutoLoad.class.php';
rareAutoLoad::register($option);

//重新替换为相对路径
$classes=require $autoLoadFile;
$_l=strlen($rootDir);
$phpData="<?php\n/**\n*autoLoadCache\n*@since ".date('Y-m-d H:i:s')."\n*/\n";
$phpData.="\$_rootDir=dirname(dirname(dirname(__FILE__)));\n";
$phpData.="return array(\n";
foreach ($classes as $_name=>$_path){
    echo $_name."===>{$_path}\n";
  $phpData.="\t'{$_name}'=>\$_rootDir.'/".substr($_path, $_l)."',\n";
}
$phpData.=");";

file_put_contents($autoLoadFile, $phpData,LOCK_EX);

echo "class scan finish!\n";
