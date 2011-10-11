<?php
/**
 *  执行使用rare框架编写应用的指定的模块 
 * @author duwei<duv123@gmail.com>
 *  
 */

/**
 * 执行使用rare框架编写应用的指定的模块
 * @param string $indexFile 入库文件路径
 * @param string $uri 要执行的模块 如 index/index?id=1
 * @param string $urlPrex url地址前缀，可空 如 http://rare.hongtao3.com/ 或者 http://rare.hongtao3.com:8080/ 使用该参数以模拟直接在该地址运行的程序
 * @param string $method 请求方式 默认get
 * @example
 * include '../../lib/rare/cli.php';
 *  rcli(realpath('../web/index.php'),"cron/orderTimeout","http://yzsp.pujia.com/");
 */
function rcli($indexFile,$uri,$urlPrex='',$method='get'){
  $pid=pcntl_fork();
  if($pid=="-1"){
    echo "could not fork\n";
  }else if($pid){
    pcntl_wait($status);
  }else{
      $pathinfo=pathinfo($indexFile);
      $baseName=$pathinfo['basename'];
      if(empty($urlPrex)){
          $_SERVER['SERVER_NAME']="localhost";
          $_SERVER['SERVER_PORT']=80;
          $_SERVER['REQUEST_URI']="/".$uri;
          $_SERVER['SCRIPT_NAME']="/".$baseName;
      }else{
          $prex=parse_url($urlPrex);
          $_SERVER['SERVER_NAME']=$prex['host'];
          $_SERVER['SERVER_PORT']=isset($prex['port'])?$prex['port']:80;
          $_path="/".trim($prex['path'],"/")."/";
          $_path=str_replace("//", "/", $_path);
          $_SERVER['REQUEST_URI']=$_path.$uri;
          $_SERVER['SCRIPT_NAME']=$_path.$baseName;
      }
     
      $_SERVER['REQUEST_METHOD']=strtoupper($method);
      chdir(dirname($indexFile));
      include_once $indexFile;
  }
}

/**
 * 直接使用命令调用 格式如下
 * php cli.php   入口文件路径                 模块地址               url地址前缀
 * php cli.php ../../admin/web/index.php cron/orderTimeout
 * php cli.php ../../admin/web/index.php cron/orderTimeout  http://rare.hongtao3.com/
 */
if(realpath($_SERVER['argv'][0])==dirname(__FILE__).'/cli.php'){
  if($_SERVER['argc']>2){
     $indexFile=realpath($_SERVER['argv'][1]);
     $uri=$_SERVER['argv'][2];
     $urlPrex=isset($_SERVER['argv'][3])?$_SERVER['argv'][3]:"";
     rcli($indexFile, $uri,$urlPrex);
  }else{
     print "\n=========================run app with cli==============================\n";
     print "命令格式:\033[34m php cli.php   入口文件路径  模块地址  url地址前缀\033[0m\n"; 
     print "eg0:\033[32m php cli.php ../../admin/web/index.php cron/orderTimeout\033[0m\n" ;
     print "eg1:\033[32m php cli.php ../../admin/web/index.php cron/orderTimeout http://rare.hongtao3.com/\033[0m\n" ;
     print "eg2:\033[32m php cli.php ../../admin/web/index.php cron/orderTimeout http://rare.hongtao3.com:8080/appName/\033[0m\n" ;
     print "标准方式:\033[34m 请查看cli.php 源代码的rcli_standard 方法 \033[0m\n"; 
     print "\n";
  }
}

/**
 *更加轻量基本的直接运行脚本：提供类注册 
 * @param $appDir app程序根目录，不是程序根目录
 * @param $serverInfo 扩展的自定义 $_SERVER 信息
 * @example
 *  job1.php 
 <?php
    include '../lib/rare/cli.php';//包含当前的这个文件
    rcli_standard("../front/");//进行类自动加载，程序初始化处理
   //===========================================================
   //下面的代码就是具体的业务逻辑了，在普通web方式能正常使用的所有功能都和之前一样使用
    $user=member::getByUserName('admin');
    var_dump($user);
 * 
 * 
 */
function rcli_standard($appDir,$serverInfo=array()){
  $appDir=rtrim(realpath($appDir),"/")."/";
  $rootDir=dirname($appDir)."/";
  $option=array('dirs'=>$appDir."lib/,".$rootDir."lib/",
                'cache'=>$appDir."cache/cli_autoload");
  include dirname(__FILE__).'/rareMVC.class.php';
  include dirname(__FILE__).'/rareAutoLoad.class.php';
  rareAutoLoad::register($option);
  $_SERVER['SERVER_NAME']="localhost";
  $_SERVER['SERVER_PORT']=80;
  $_SERVER['REQUEST_URI']="/";
  $_SERVER['SCRIPT_NAME']="/index.php";
  foreach ($serverInfo as $_k=>$_v){
     $_SERVER[$_k]=$_v;
  }
  
  /**
   * 此处只是注册而不进行运行。过滤器，路由解析均不会
   */
  rareContext::createApp($appDir);
}

