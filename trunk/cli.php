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
      include_once $indexFile;
  }
}

/**
 * 直接使用命令调用 格式如下
 * php cli.php   入口文件路径                 模块地址               url地址前缀
 * php cli.php ../../admin/web/index.php cron/orderTimeout
 * php cli.php ../../admin/web/index.php cron/orderTimeout  http://rare.hongtao3.com/
 */
if($_SERVER['argv'][0]=='cli.php'){
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
     print "eg1:\033[32m php cli.php ../../admin/web/index.php cron/orderTimeout http://rare.hongtao3.com:8080/appName/\033[0m\n" ;
     print "\n";
  }
}

