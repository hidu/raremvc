<?php
/**
 *  rareMVC
 *   
 * @author duwei duv123@gmail.com
 * @version 1.0 a
 */

/**
 * app 入口类
 */
class rareContext{
    
    private $appDir;
    
    private $webRoot;
    private $webRootUrl;
    private $moduleName;
    private $actionName;
    
    private $uri;
    private $scriptName;
    private $isScriptNameInUrl=false;
    
    
    private static $instance;
    
    public function __construct($appDir){
        $this->appDir=$appDir;
        date_default_timezone_set('Asia/Shanghai');
        header("Content-Type:text/html; charset=utf-8");
    }
      //是否是https       
     public function isSecure(){
         return (
           (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == 1))
            ||
          (isset($_SERVER['HTTP_SSL_HTTPS']) && (strtolower($_SERVER['HTTP_SSL_HTTPS']) == 'on' || $_SERVER['HTTP_SSL_HTTPS'] == 1))
            ||
          (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')
         );
      }
    //创建一个rare app实例，在这里会注册类自动装载
    public static function createApp($appDir=''){
        if(!$appDir){
            $trace=debug_backtrace(true);
            $appDir=dirname(dirname($trace[0]['file']))."/";
         }
        self::$instance=new rareContext($appDir);
        $class_autoload=rareConfig::get("class_autoload",true);
         if($class_autoload){
            include dirname(__FILE__).'/rareAutoLoad.class.php';
            $class_autoloadOption_default=array('dirs'=>self::$instance->getAppLibDir(),
                                               'cache'=>self::$instance->getCacheDir()."classAutoLoad.php");
            $option=array_merge($class_autoloadOption_default,rareConfig::get("class_autoload_option",array()));
            rareAutoLoad::register($option);
         }
        return self::$instance;
    }
    /**
     * @return rareContext
     */
    public static function getContext(){
        return self::$instance;
    }
    //运行程序，解析url地址、执行过滤器、执行动作方法等    
    public function run(){
        $this->parseRequest();
        $this->executeFilter();    
        $this->executeActtion($this->uri);
    }
    //解析url地址
    private function parseRequest(){
        $this->webRoot=substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],"/")+1);
        $pathInfo=pathinfo($_SERVER['SCRIPT_NAME']);
        $this->scriptName=$pathInfo["basename"];
        $this->setWebRootUrl();
        
        $requestUri=$_SERVER['REQUEST_URI'];
        $this->uri=substr($requestUri, strlen($this->webRoot));
        $scriptNamelen=strlen($this->scriptName);
        if(substr($this->uri, 0,$scriptNamelen)==$this->scriptName){
           $this->uri=substr($this->uri, strlen($this->scriptName)+1);
           $this->isScriptNameInUrl=true;
         }
        $this->uri=$this->uri?$this->uri:"index/index";
    }
    //计算程序完整的url地址
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
    //内部跳转 执行指定的动作 action同名时会出错(类重名)。
    public  function forward($uri){
        $this->executeActtion($uri);
        die;
    }
    //执行指定动作
    public  function executeActtion($uri){
        $uriInfo=$this->parseActionUri($uri);
        $this->moduleName=$uriInfo['m'];
        $this->actionName=$uriInfo['a'];
        $query=$uriInfo['q'];
        
        $actionFile=$this->getModuleDir().$this->moduleName."/action/".$this->actionName.".php";
        chdir(dirname($actionFile));
        
        include($actionFile);
        $actionClass=$this->actionName."Action";
        $action = new $actionClass($this->moduleName,$this->actionName);
        $action->preExecute();
        $result=$action->execute();
        if($result!=null && empty($result))return;
        $action->display($result);        
    }
    //将当前的url解析为action 方便识别的数组格式
    public function parseActionUri($uri){
        $tmp=parse_url($uri);
        if(empty($tmp['path']))$tmp['path']='index';
        $tmp['path']=preg_replace("/\.\w*/", "", $tmp['path']);
        $path=explode("/",$tmp['path']);
        if(empty($path[1]))$path[1]='index';
        $uriInfo=array();
        $uriInfo['m']=$path[0]=="~"?$this->moduleName:$path[0];
        $uriInfo['a']=$path[1]=="~"?$this->actionName:$path[1];
        $uriInfo['q']=empty($tmp['query'])?"":$tmp['query'];
        $uriInfo["u"]=$uriInfo['m']."/".$uriInfo['a'];
        return $uriInfo;
    }
    //执行过滤器
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
       return $this->getAppDir()."lib/"; 
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
    public function getWebRoot(){
      return $this->webRoot;
    }
    
    public function getScriptName(){
      return $this->scriptName;
    }
    
    public function isScriptNameInUrl(){
     return $this->isScriptNameInUrl;
    }
    
    public function getConfigDir(){
        return $this->getAppDir()."config/";
    }
    public function getCacheDir(){
      return rareConfig::get('cache_dir',$this->getAppDir()."cache/");
    }
    //是否是ajax 请求
    public static function isXmlHttpRequest(){
       return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
     }
}

/**
 * 模板相关操作 
 */
class rareView{
    /**
     * 将数据渲染到模板中去
     * @param array $vars
     * @param string $viewFile 模板文件路径
     */
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
    /**
     * 设置页面title
     * @param string $title
     * @param boolean $clean 是否将title 完全重新设置，为false 表示新添加的补充到当前标题的前面
     */
    public static function setTitle($title,$clean=false){
        if(!$clean){
            $title=$title."--".rareConfig::get("title","rare app");
          }
         rareConfig::set('title', $title);
    }
    /**
     *添加js 文件 
     * @param $js  js文件地址
     * @param $index 显示顺序
     */
    public static function addJs($js,$index=999){
        $jss=rareConfig::get("js");
        if(!is_array($jss))$jss=explode(",", $jss);
        $jss[$js]=$index;
        rareConfig::set('js', $jss);
    }
    /**
     * 添加一个css 文件到head 标签中
     * @param $css
     * @param $index  显示顺序
     */
    public static function addCss($css,$index=999){
        $csss=rareConfig::get("css",array());
        $csss[$css]=$index;
        rareConfig::set('css', $csss);
    }
    /**
     * 供模板调用的输出css 和js 链接的方法
     */
    public static function include_js_css(){
       function _fill_url($_uri){
           return (substr($_uri,0,1)=="/" || substr($_uri, 0,7)== 'http://' ||substr($_uri, 0,8)== 'https://')?$_uri:public_path($_uri); 
        }
        function _url_index($arr){
            if(!is_array($arr))$arr=explode(",", $arr);
            foreach ($arr as $_k=>$_v){
                if(is_numeric($_k)){
                    $arr[_fill_url($_v)]=999+$_k;
                    unset($arr[$_k]);
                  }else{
                      $arr[_fill_url($_k)]=$_v;
                      unset($arr[$_k]);
                  }
             }
            asort($arr);
            return $arr;
        }
        $csss=rareConfig::get("css",array());
        $csss=_url_index($csss);
        foreach ($csss as $css=>$index){
            echo "<link rel=\"stylesheet\" href=\"{$css}\" type=\"text/css\" media=\"screen\" />\n";
         }
         
        $jss=rareConfig::get("js",array());
        $jss=_url_index($jss);
        foreach ($jss as $js=>$index){
            echo "<script type=\"text/javascript\" src=\"{$js}\"></script>\n";
         }
    }
    
    public static function include_title(){
      echo "<title>".htmlspecialchars(rareConfig::get("title","rare app"))."</title>";
    }
}

/**
 *动作类 
 */
abstract class rareAction{
    protected  $context;
    protected  $layout=null;
    public $vars;
    private $viewFile;
    protected  $moduleName;
    protected  $actionName;
    private $isRender=false;
    
   public function __construct($moduleName,$actionName){
      $this->context=rareContext::getContext();
      $this->moduleName=$moduleName;
      $this->actionName=$actionName;
      $this->viewFile=$this->context->getModuleDir().$moduleName."/view/".$actionName.".php";
    }
    /**
     *execute 前执行的方法 
     */
   public function preExecute(){}
   /**
    *动作的入口 
    */
   abstract function execute();
   
    public function forward($uri){
        $this->context->forward($uri);
    }
    /**
     * 设置使用那个模板文件 默认使用的是default
     * 模板文件目录位置为 templates/layout/
     * @param mixd $layout string 类型时使用指定模板,为false 表示不需要模板
     */
    public function setLayout($layout){
      $this->layout=$layout;
    }
    /**
     *获取模板文件的路径 
     */
   private  function getLayoutFile(){
      if(false===$this->layout || rareContext::isXmlHttpRequest())return null;
      if(null==$this->layout){
          $layoutFile=$this->context->getLayoutDir().$this->context->getModuleName().".php";
          if(!file_exists($layoutFile)){
             $layoutFile=$this->context->getLayoutDir()."default.php";
          }
      }else{
        $layoutFile=$this->context->getLayoutDir().$this->layout.".php";
      }
      return $layoutFile;
    }
    
    /**
     * 渲染模板
     * @param $viewFile
     */
    public function display($viewFile=null){
        if($this->isRender)return;
        $this->isRender=true;
        if(!empty($viewFile) && is_string($viewFile)){
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
/**
 * 配置操作类，设置、读者指定的配置文件
 * @author duwei
 */
class rareConfig{
   private  static $configs=array(); 
   public static function getAll($configName='default'){
       if(isset(self::$configs[$configName])){
          return self::$configs[$configName];
        }
       $file=rareContext::getContext()->getAppDir()."config/".$configName.".php";
       $config=array();
       if(file_exists($file)){
          $config=require $file;
        }
       self::$configs[$configName]=$config;
       return $config;
   }
   
   public static function get($item,$default=null,$configName="default"){
       $config=self::getAll($configName);
       return isset($config[$item])?$config[$item]:$default;
   }
   
   public static function set($item,$val,$configName='default'){
       self::getAll($configName);
       self::$configs[$configName][$item]=$val;
   }
}

/**
 * 将程序内部地址转换为外面地址
 * 如 url('hello/index?a=1') 输出地址为http://127.0.0.1/appName/hello.html?a=1
 * @param string $uri
 * @param string $suffix
 */
function url($uri,$suffix=""){
     $context=rareContext::getContext();
     $url=public_path("/");
     if(($context->isScriptNameInUrl() && $context->getScriptName() != 'index.php' )|| !rareConfig::get("no_script_name",true)){
        $url.=$context->getScriptName()."/";
     }
     $suffix=$suffix?$suffix:rareConfig::get('suffix','html');
     $uri=ltrim($uri,"/");
     $tmp=parse_url($uri);
     $uri=str_replace("/index", "", $tmp['path']).".".$suffix.(isset($tmp['query'])?"?".$tmp['query']:'');
     return $url.$uri;
}
/**
 * 输出web 相对根目录的地址
 * 如 public_path('js/hello.js') 输出为http://127.0.0.1/appName/js/hello.js
 * @param string $uri
 */
function public_path($uri){
    if(rareConfig::get('url_http_host',false)){
       return rareContext::getContext()->getWebRootUrl().ltrim($uri,"/");
    }else{
       return rareContext::getContext()->getWebRoot().ltrim($uri,"/");
    }
}

/**
 * 获取一个组件
 * 组件的位置在templates/component/
 * @param string $name  组件名称
 * @param array $param  参数 
 */
function fetch($name,$param=null){
     $componentFile=rareContext::getContext()->getComponentDir().trim($name,"/").".php";
     return rareView::render($param, $componentFile);
}
/**
 * 调用简单的helper 文件，文件如lib/helper/hello.php
 * @param $helper
 */
function use_helper($helper){
    static $helpers=array();
    if(in_array($helper, $helpers))return;
    $helperFile=rareContext::getContext()->getAppLibDir()."helper/".$helper.".php";
    if(!file_exists($helperFile))die("can not find helper ".$helperFile);
    include $helperFile;
    $helpers[$helper]=1;
}