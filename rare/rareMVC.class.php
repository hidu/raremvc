<?php
/**
 *  rareMVC
 *   
 * @author duwei duv123@gmail.com
 * @version 1.0 a
 */

class rareContext{
    
    private $appDir;
    
    private $webRoot;
    private $webRootUrl;
    private $moduleName;
    private $actionName;
    
    private $uri;
    private $scriptName;//脚本名称 如index.php
    private $isScriptNameInUrl=false;
    
    private static $instance;
    
    public function __construct($appDir){
        $this->webRoot=substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],"/")+1);
        $pathInfo=pathinfo($_SERVER['SCRIPT_NAME']);
        $this->scriptName=$pathInfo["basename"];
        
        $this->appDir=$appDir;
        $this->setWebRootUrl();    
        date_default_timezone_set('Asia/Shanghai');
    }
    
    private function setWebRootUrl(){
        $webRootUrl= 'http://'.$_SERVER['HTTP_HOST'];
        if(80 != $_SERVER['SERVER_PORT'] ){
            if($this->isSecure()){
              $webRootUrl = 'https://'.$_SERVER['HTTP_HOST'];
            }else{
              $webRootUrl.=$_SERVER['SERVER_PORT'];
            }
         }
        $webRootUrl.=$this->webRoot;
        $this->webRootUrl=$webRootUrl;
    }
    
     public function isSecure(){
         return (
           (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == 1))
            ||
          (isset($_SERVER['HTTP_SSL_HTTPS']) && (strtolower($_SERVER['HTTP_SSL_HTTPS']) == 'on' || $_SERVER['HTTP_SSL_HTTPS'] == 1))
            ||
          (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')
         );
      }
    
    public static function createApp(){
        $trace=debug_backtrace(true);
        $appDir=dirname(dirname($trace[0]['file']))."/";
        self::$instance=new rareContext($appDir);
        return self::$instance;
    }
    /**
     * @return rareContext
     */
    public static function getContext(){
        return self::$instance;
    }
    
    public function run(){
        $this->parseRequest();
        $this->executeFilter();    
        $this->executeActtion($this->uri);
    }
    
    private function parseRequest(){
        $requestUri=$_SERVER['REQUEST_URI'];
        $this->uri=substr($requestUri, strlen($this->webRoot));
        $scriptNamelen=strlen($this->scriptName);
        if(substr($this->uri, 0,$scriptNamelen)==$this->scriptName){
           $this->uri=substr($this->uri, strlen($this->scriptName)+1);
           $this->isScriptNameInUrl=true;
         }
        $this->uri=$this->uri?$this->uri:"index/index";
//        dump($requestUri);
//        dump($this->webRoot);
//        dump($this->uri);
//        dump($_SERVER);
    }

    public  function forward($uri){
        $this->executeActtion($uri);
        die;
    }
    
    public  function executeActtion($uri){
        $uriInfo=$this->parseActionUri($uri);
        $this->moduleName=$uriInfo['m'];
        $this->actionName=$uriInfo['a'];
        
        $query=$uriInfo['q'];
        
        $actionFile=$this->getAppDir()."/module/".$this->moduleName."/action/".$this->actionName.".php";
        chdir(dirname($actionFile));
        
        include($actionFile);
        $actionClass=$this->actionName."Action";
        $action = new $actionClass($this->moduleName,$this->actionName);
        $action->preExecute();
        $result=$action->execute();
        if($result!=null && empty($result))return;
       $action->display();        
    }
    
    public function parseActionUri($uri){
        $tmp=parse_url($uri);
        $path=explode("/",$tmp['path']);
        if(empty($path[1]))$path[1]='index';
        $uriInfo=array();
        $uriInfo['m']=$path[0]=="~"?$this->moduleName:$path[0];
        $uriInfo['a']=$path[1]=="~"?$this->actionName:$path[1];
        $uriInfo['q']=empty($tmp['query'])?"":$tmp['query'];
        $uriInfo["u"]=$uriInfo['m']."/".$uriInfo['a'];
        return $uriInfo;
    }
    
    
   private function executeFilter(){
          $filterFile=$this->getAppLibDir()."myFilter.class.php";
          if(file_exists($filterFile)){
               include $filterFile;
               new myFilter($this);
          }
    }
    
    public function getAppDir(){
       return $this->appDir;
    }
    public function getAppLibDir(){
       return $this->getAppDir()."/lib/"; 
    }

    public function getModuleName(){
        return $this->moduleName;
    }
    
    public function getActionName(){
       return $this->actionName;
    }
    
    public function getTemplatesDir(){
       return $this->getAppDir()."templates/";
    }
    public function getLayoutDir(){
        return $this->getTemplatesDir()."layout/";
    }
    public function getModuleDir(){
        return $this->getAppDir()."module/";
    }
    
    public function getComponentDir(){
       return $this->getTemplatesDir()."component/";
    }
    
    public function getWebRootUrl(){
      return $this->webRootUrl;
    }
    
    public function getScriptName(){
      return $this->scriptName;
    }
    
    public function isScriptNameInUrl(){
     return $this->isScriptNameInUrl;
    }
}

class rareView{
   public static function render($vars,$viewFile){
        $currentPWD = getcwd();
        chdir(dirname($viewFile));
        if(is_string($vars))parse_str($vars,$vars);
        if(is_array($vars))extract($vars);
        ob_start();
        include $viewFile;
        $content= ob_get_contents();
        ob_end_clean();
        chdir($currentPWD);
        return $content;
    }
}


abstract class rareAction{
    protected  $context;
    protected  $layout=null;
    public $vars;
    private $viewFile;
    protected  $moduleName;
    protected  $actionName;
    
   public function __construct($moduleName,$actionName){
      $this->context=rareContext::getContext();
      $this->moduleName=$moduleName;
      $this->actionName=$actionName;
      $this->viewFile=$this->context->getModuleDir()."/".$moduleName."/view/".$actionName.".php";
    }
   public function preExecute(){}
   abstract function execute();
   
    public function forward($uri){
        $this->context->forward($uri);
    }
    
    public function setLayout($layout){
      $this->layout=$layout;
    }
   private  function getLayoutFile(){
      if(false===$this->layout)return null;
      if(null==$this->layout){
          $layoutFile=$this->context->getLayoutDir().$this->context->getModuleName().".php";
          if(!file_exists($layoutFile)){
             $layoutFile=$this->context->getLayoutDir()."default.php";
          }
      }else{
        $layoutFile=$this->context->getLayoutDir().$this->layout.".php";
      }
    }
    
    
    public function display($viewFile=null){
        if($viewFile){
               $pathArray=explode("/",$viewFile);
               $moduleName=$pathArray[0]=="~"?$this->moduleName:$pathArray[0];
               array_shift($pathArray);
               $this->viewFile=$this->context->getModuleDir().$moduleName."/view/".join("/", $pathArray).".php";
         }
        if(!file_exists($this->viewFile))return;
        $content=rareView::render($this->vars, $this->viewFile);
        $layoutFile=$this->getLayoutFile();
        if($layoutFile){
            chdir(dirname($layoutFile));
            include($layoutFile);
        }else{
            echo $content;
         }
    }
}

class rareConfig{
   public static function getAll($configName='default'){
       static $configs=array();
       if(isset($configs[$configName])){
          return $configs[$configName];
        }
       $file=rareContext::getContext()->getAppDir()."config/".$configName.".php";
       $config=array();
       if(file_exists($file)){
          $config=require $file;
        }
       $configs[$configName]=$config;
       return $config;
   }
   
   public static function get($item,$default=null,$configName="default"){
       $config=self::getAll($configName);
       return isset($config[$item])?$config[$item]:$default;
   }
}

function url($uri){
     $context=rareContext::getContext();
     $url=$context->getWebRootUrl();
     if(($context->isScriptNameInUrl() && $context->getScriptName() != 'index.php' )|| !rareConfig::get("no_script_name",true)){
        $url.=$context->getScriptName()."/";
     }
     return $url.ltrim($uri,"/");
}

function public_path($uri){
     return rareContext::getContext()->getWebRootUrl().ltrim($uri,"/");
}

function fetch($name,$param=null){
     $componentFile=rareContext::getContext()->getComponentDir().trim($name,"/").".php";
     return rareView::render($param, $componentFile);
}