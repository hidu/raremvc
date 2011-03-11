<?php
/**
 * rareMVC
 * @package rare
 * @author duwei duv123@gmail.com
 * @version 1.2
 */

/**
 * app 入口类
 */
!defined("PROD") && define("PROD",0);                //是否是生产环境默认为否,对自定义错误页面等会有影响
class rareContext{

    private $appDir;                                 //当前app所在的目录
    private $rootDir;                                //当前程序的根目录，应该是appDir的上一级目录

    private $webRoot;                                //相对的程序根路径 eg /rare/
    private $webRootUrl;                             //完整的程序的地址 eg http://127.0.0.1/rare/
    private $moduleName;
    private $actionName;

    private $uri;
    private $scriptName;                             //入口脚本名称 如index.php
    private $isScriptNameInUrl=false;                //url中是否包含入口文件
    private $appName;                                //当前app的名称
    private $version='1.2 20110311';                 //当前框架版本
    private $cacheDir="";                            //cache目录
    private $filter=null;                            //过滤器

    private static $instance;                         //app实例    
        
    public function __construct($appDir){
        $this->appDir=$appDir;
        $this->appName=basename($this->appDir);
        $this->rootDir=dirname($this->appDir)."/";
        header("rareMVC:".$this->version);
    }
    
    //创建一个rare app实例，在这里会注册类自动装载
    public static function createApp($appDir=''){
        if(!$appDir){
            $trace=debug_backtrace(true);
            $appDir=dirname(dirname($trace[0]['file']))."/";
        }
        self::$instance=new rareContext($appDir);
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
        $this->regShutdown();
        $this->init();
        $this->parseRequest();
        $this->regAutoLoad();
        $this->executeFilter("doFilter");
        $this->executeActtion($this->uri);
    }
    
    private function init(){
        date_default_timezone_set(rareConfig::get('timezone','Asia/Shanghai'));
        header("Content-Type:text/html; charset=".rareConfig::get('charset','utf-8'));
        rareConfig::set('cache_dir',rareConfig::get("cache_dir",$this->getRootDir()."cache/")."app_".$this->getAppName()."/");
        define('RARE_CACHE_DIR', $this->getCacheDir());
    }
    
    //注册shutdown 事件，当发生致命错误时执行error500方法或者打印出错信息
    private function regShutdown(){
        function _rare_shutdown_catch_error(){
            $_error=error_get_last();
            if($_error && in_array($_error['type'],array(1,4,16,64,256,4096,E_ALL))){
              rareContext::getContext()->error500($_error);
            }
        }
        register_shutdown_function("_rare_shutdown_catch_error");
    }
    //注册class auto load
    private function regAutoLoad(){
        $class_autoload=rareConfig::get("class_autoload",true);
        if($class_autoload){
            include dirname(__FILE__).'/rareAutoLoad.class.php';
            $_autoloadOption=array('dirs'=>$this->getRootLibDir().",".$this->getAppLibDir(),
                                   'cache'=>$this->getCacheDir().$this->getAppName()."_classAutoLoad"
                                   );
            if(isset($_autoloadOption['hand']) && $_autoloadOption['hand']){
               $_autoloadOption['cache']=$this->getConfigDir()."autoLoad";
              }
            $option=array_merge($_autoloadOption,rareConfig::get("class_autoload_option",array()));
            rareAutoLoad::register($option);
        }
    }
    //解析url地址
    private function parseRequest(){
        $this->webRoot=substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],"/")+1);
        $pathInfo=pathinfo($_SERVER['SCRIPT_NAME']);
        $this->scriptName=$pathInfo["basename"];
        $this->webRootUrl=rare_httpHost().$this->webRoot;

        $requestUri=$_SERVER['REQUEST_URI'];
        $this->uri=trim(substr($requestUri, strlen($this->webRoot)),"/");
        $scriptNamelen=strlen($this->scriptName);
        if(substr($this->uri, 0,$scriptNamelen)==$this->scriptName){
            $this->uri=substr($this->uri, strlen($this->scriptName)+1);
            $this->isScriptNameInUrl=true;
        }
        $this->uri=$this->uri?$this->uri:"index/index";
        include_once dirname(__FILE__).'/rareRouter.class.php';
        rareRouter::init();
        $this->uri=rareRouter::parse($this->uri);
        $uriInfo=$this->parseActionUri($this->uri);
        $this->moduleName=$uriInfo['m'];
        $this->actionName=$uriInfo['a'];
    }
    
    public function error404(){
        @header('HTTP/1.0 404');
         $this->goError(404);      
         $this->_errorPage("404 Not Found","The requested URL <b>{$_SERVER['REQUEST_URI']}</b> was not found on this server.");
     }
    //500错误
    public function error500($_error=array()){
        @header('HTTP/1.0 500');
         if(PROD){
           $this->goError(500);
           $this->_errorPage("500 Internal Server Error", "");
         }else{
             $this->_errorPage("500 Internal Server Error", "<pre>".$_error['message']."</pre> in file ".$_error['file']." at line ".$_error['line']);
         }      
    }
    private function _errorPage($title,$msg){
        ob_clean();
        $html="<!DOCTYPE html><html><head><meta http-equiv='content-type' content='text/html;charset=".rareConfig::get('charset','utf-8')."'>".
               "<title>{$title}</title></head><body><p style='margin-top:15px;background:#3366cc;color:white'>Error</p>".
               "<h1>{$title}</h1>{$msg}<br/><br/><a href='".public_path("")."'>Go Home</a><p style='background:#3366cc;height:4px'>&nbsp;</p></body></html>";
        die($html);
    } 
     /**
      * 当http错误发生时，跳转到错误页面。
      * 比如默认404 的错误页面为error/e404,当时当该action不存在时则不进行操作。
      * 或者 也可以在配置文件中定义404页面：$config['error404']='http://www.exmaple.com/error.html';
      * @param int $code
      */
     public function goError($code){
           ob_clean();
            $errorUri=rareConfig::get('error'.$code,'error/e'.$code);
            if(_rare_isUrl($errorUri)){
                redirect($errorUri);              
            }else{
                $tmp=explode("/",$errorUri);
                if($tmp[0] != $this->moduleName && $tmp[1] != $this->actionName && $this->isActionExist($tmp[0],$tmp[1])){
                   forward($errorUri);                         
                  }                       
              }  
       }
     
     public function isActionExist($moduleName,$actionName){
         $actionFile=$this->getActionFile($moduleName,$actionName);
         return file_exists($actionFile);
      }
     
     private function getActionFile($moduleName,$actionName){
        return  $this->getModuleDir().$moduleName."/action/".$actionName.".php";
       }      
    /**
     * 执行指定动作
     * $uri可以是 demo/index?a=123
     */
    public  function executeActtion($uri){
        $uriInfo=$this->parseActionUri($uri);
        $this->moduleName=$uriInfo['m'];
        $this->actionName=$uriInfo['a'];
        if($uriInfo['q']){
           parse_str($uriInfo['q'],$query);
           foreach ($query as $_k=>$_v){
               $_GET[$_k]=$_v;
               $_REQUEST[$_k]=$_v;
             }
         }
        
        $actionFile=$this->getActionFile($this->moduleName,$this->actionName);
        if(!file_exists($actionFile)){
             $this->error404();          
          }
        
        $this->executeFilter("beforeExecute");//执行具体动作前执行过滤器指定方法
        
        chdir(dirname($actionFile));
            
        include($actionFile);
        $actionClass=$this->actionName."Action";
        if(class_exists($this->moduleName.'_'.$actionClass)){
           $actionClass=$this->moduleName.'_'.$actionClass;
        }
        
        $action = new $actionClass($this->moduleName,$this->actionName);
        $action->preExecute();
        
        $restFn="execute".ucfirst(strtolower($_SERVER["REQUEST_METHOD"]));
        if(method_exists($action, $restFn)){
            $result=call_user_func(array($action,$restFn));
         }else{
           $result=$action->execute();
         }
        if($result!=null && empty($result))return;
        $action->display($result);
    }
    //将当前的url解析为action 方便识别的数组格式
    public function parseActionUri($uri){
        $tmp=parse_url($uri);
        if(empty($tmp['path']))$tmp['path']='index';
        $tmp['path']=preg_replace("/\.\w*$/", "", $tmp['path']);
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
    private function executeFilter($method=''){
        if($this->filter ===false)return;
        if($this->filter==null){
            $filterFile=$this->getAppLibDir()."myFilter.class.php";
            if(file_exists($filterFile)){
                include $filterFile;
                $this->filter=new myFilter();
             }else{
                 $this->filter=false;return;
              }
        }
        if(!empty($method) && method_exists($this->filter, $method)){
            $this->filter->$method();
        }
    }

    public function getAppDir(){
        return $this->appDir;
    }
    
    public function getRootDir(){
         return $this->rootDir;
    }
    //获取当前app的名称 比如demo
    public function getAppName(){
      return $this->appName;
    }
    public function getAppLibDir(){
        return $this->getAppDir()."lib/";
    }
    public function getRootLibDir(){
         return $this->rootDir."lib/";
    }

    public function getModuleName(){
        return $this->moduleName;
    }

    public function getActionName($full=false){
        return $full?$this->moduleName."/".$this->actionName:$this->actionName;
    }

    public function getLayoutDir(){
        return $this->getAppDir()."layout/";
    }
    public function getModuleDir(){
        return $this->getAppDir()."module/";
    }

    public function getComponentDir(){
        return $this->getAppDir()."component/";
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
    //url中是否包含脚本名称 如/rare/index.php/demo 为true
    public function isScriptNameInUrl(){
        return $this->isScriptNameInUrl;
    }

    public function getConfigDir(){
        return $this->getAppDir()."config/";
    }
    
    public function getCacheDir(){
        return rareConfig::get("cache_dir");
    }
    public function getRequestUri(){
        return $this->uri;
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
        $rare_currentPWD = getcwd();
        chdir(dirname($viewFile));
        if(is_string($vars))parse_str($vars,$vars);
        $vars['rare_vars']=$vars;
        if(is_array($vars))extract($vars);
        ob_start();
        try{
          include $viewFile;
        }catch(Exception $e){}
        $content= ob_get_contents();
        ob_end_clean();
        chdir($rare_currentPWD);
        return $content;
    }
    /**
     * 设置页面title
     * @param string $title
     * @param boolean $clean 是否将title 完全重新设置，为false 表示新添加的补充到当前标题的前面
     */
    public static function setTitle($title,$clean=false){
        if(!$clean) $title=$title."-".rareConfig::get("title","rare app");
        rareConfig::set('title', $title);
    }
    /**
     *添加js 文件
     * @param $js  js文件地址
     * @param $index 显示顺序
     */
    public static function addJs($js,$index=null){
      self::_staticIndex("js", $js,$index);
    }
    /**
     * 添加一个css 文件到head 标签中
     * @param $css
     * @param $index  显示顺序
     */
    public static function addCss($css,$index=null){
         self::_staticIndex("css", $css,$index);
    }
    /**
     * 添加一个css 文件到head 标签中
     * @param $css
     * @param $index  显示顺序
     */
    private static function _staticIndex($type,$uri,$index=null){
        $tmp=rareConfig::get($type,array());
        if(!is_array($tmp))$tmp=explode(",", $tmp);
        if(is_numeric($index)){
            $index=(int)$index;
           if($index>=count($tmp)){
              $tmp[]=$uri;
            }else{
              $_tmp=array_slice($tmp, 0,$index);
              $_tmp[]=$uri;
              $tmp=array_merge($_tmp,array_slice($tmp, $index));
             }
        }else{
           $tmp[]=$uri;
         }
       rareConfig::set($type, $tmp);
    }
    /**
     * 供模板调用的输出css 和js 链接的方法
     */
    public static function include_js_css(){
        function _rare_fill_url($_uris){
            if(is_string($_uris))$_uris=explode(",", $_uris);
            $tmp=array();
            foreach($_uris as $_uri){
              $_uri=trim($_uri);
              if(!$_uri)continue;
              $tmp[]=_rare_isUrl($_uri)?$_uri:public_path($_uri);
            }
            return array_unique($tmp);
        }
        $csss=_rare_fill_url(rareConfig::get("css",array()));
        _rare_runHook("css", array(&$csss));
        $cssVersion=rareConfig::get("cssVersion",null);
        foreach ($csss as $css){
            $css.=$cssVersion?"?version=".$cssVersion:"";
            echo "<link rel=\"stylesheet\" href=\"{$css}\" type=\"text/css\" media=\"screen\" />\n";
        }
         
        $jss=_rare_fill_url(rareConfig::get("js",array()));
        _rare_runHook('js', array(&$jss));
        $jsVersion=rareConfig::get("jsVersion",null);
        foreach ($jss as $js){
            $js.=$jsVersion?"?version=".$jsVersion:"";
            echo "<script type=\"text/javascript\" src=\"{$js}\"></script>\n";
        }
    }

    public static function include_title(){
        echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".rareConfig::get('charset','utf-8')."\" />\n";
        echo "<title>".htmlspecialchars(rareConfig::get("title","rare app"))."</title>\n";
         if($keywords=rareConfig::get('meta.keywords'))echo "<meta name=\"keywords\" content=\"".htmlspecialchars($keywords)."\" />\n";
         if($description=rareConfig::get('meta.description'))echo "<meta name=\"description\" content=\"".htmlspecialchars($description)."\" />\n";
    }
     //设置meta 的关键词
    public static function setMeta_keywords($keywords){
        rareConfig::set('meta.keywords', $keywords);   
     }
     //设置meta的描述
    public static function setMeta_description($description){
        rareConfig::set('meta.description', $description);   
     }
}

/**
 *动作类
 */
abstract class rareAction{
    protected  $context;
    protected  $layout=null;
    protected  $layoutForce=false;//是否强制在任何情况下都使用layout
    public     $vars;
    private    $viewFile;
    protected  $moduleName;
    protected  $actionName;
    private    $isRender=false;

    public function __construct($moduleName,$actionName){
        $this->context=rareContext::getContext();
        $this->moduleName=$moduleName;
        $this->actionName=$actionName;
        $this->viewFile=$this->context->getModuleDir().$moduleName."/view/".$actionName.".php";
    }
    /**
     * 获取request值
     * @param string $key  变量名称
     * @param string $default  默认值
     */
    public function getRequestParam($key,$default=null){
        return isset($_REQUEST[$key])?trim($_REQUEST[$key]):$default;
     }
    /**
     *execute 前执行的方法
     */
    public function preExecute(){}
    /**
     *动作的入口
     */
    abstract function execute();
     
   
    /**
     * 设置使用那个模板文件 默认使用的是default
     * 模板文件目录位置为 templates/layout/
     * @param mixd $layout string 类型时使用指定模板,为false 表示不需要模板
     */
    public function setLayout($layout){
        $this->layout=$layout;
    }
    /**
     * 是否强制使用模板
     * @param boolean $layoutForce
     */
    public function setLayoutForce($layoutForce=false){
        $this->layoutForce=$layoutForce;
    }
    /**
     *获取模板文件的路径
     */
    private  function getLayoutFile(){
        if(!$this->layoutForce && (false===$this->layout || rare_isXmlHttpRequest()))return null;
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
            if(count($pathArray)==1){
               $moduleName=$this->moduleName;
            }else{
               $moduleName=$pathArray[0]=="~"?$this->moduleName:$pathArray[0];
               array_shift($pathArray);
              }
            $this->viewFile=$this->context->getModuleDir().$moduleName."/view/".join("/", $pathArray).".php";
        }
        if(!file_exists($this->viewFile))return;
        $body=rareView::render($this->vars, $this->viewFile);
        $layoutFile=$this->getLayoutFile();
        if($layoutFile){
            chdir(dirname($layoutFile));
            include($layoutFile);
        }else{
            echo $body;
        }
    }
}
/**
 * 配置操作类，设置、读者指定的配置文件
 * @author duwei
 */
class rareConfig{
    private  static $configs=array();
    /**
     *  读取某个配置文件
     * @param $configName 配置文件名称 不带.php的部分
     */
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
     
    /**
     * 读取指定配置文件的指定条目的内容，若没有设置则返回默认值
     * @param string $item   条目名称
     * @param object $default   默认值
     * @param string $configName 配置文件名称
     */
    public static function get($item,$default=null,$configName="default"){
        $config=self::getAll($configName);
        return isset($config[$item])?$config[$item]:$default;
    }
     /**
      * 设置指定配置的指定条目为指定的值(只对当前请求有效，不会保存)
      * @param string $item  条目名称
      * @param object $val   设置的值
      * @param string $configName  配置文件名称
      */
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
function url($uri,$suffix="",$full=false){
    $context=rareContext::getContext();
    $urlPrex=public_path("/",$full);
    $script_name=rareConfig::get("script_name","index.php");//默认的index.php文件
    if(($context->isScriptNameInUrl() && $context->getScriptName() != $script_name )|| !rareConfig::get("no_script_name",true)){
        $urlPrex.=$context->getScriptName()."/";
    }
    
    $tmp=parse_url($uri);
    $query=array();
    if(isset($tmp['query']))parse_str($tmp['query'],$query);
      
    if(empty($tmp['path'])){
        $tmp['path']=$context->getModuleName()."/".$context->getActionName();
    }
    $uri=trim(preg_replace("/~\//", $context->getModuleName()."/",ltrim($tmp['path'],"/")),"/");
    if(!strpos($uri, "/"))     $uri.="/index";
    
    $suffix=$suffix?$suffix:rareConfig::get('suffix','html');
     _rare_runHook('url', array(&$uri,&$query,&$suffix));//run callback function
     
    $generate=rareRouter::generate($uri, $query);
    if($generate){
       list($uri,$query,$_suffix)=$generate;
        if(isset($_suffix))$suffix=$_suffix;
    }
    
    if( $uri == 'index/index') $uri="";
    $uri=preg_replace("/\/index$/", "", $uri);
    
    $queryStr=$query?"?".http_build_query($query):"";
    $suffix=($suffix && $uri && !str_endWith($uri, "/"))?(".".$suffix):"";
    return $urlPrex.$uri.$suffix.$queryStr;
}
/**
 * 输出web 相对根目录的地址
 * 如 public_path('js/hello.js') 输出为http://127.0.0.1/appName/js/hello.js
 * @param string $uri
 */
function public_path($uri,$full=false){
    if($full || rareConfig::get('url_http_host',false)){
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
    $tmp=parse_url($name);
    $name=$tmp['path'];
    if(isset($tmp['query'])){
       $param=rare_param_merge($tmp['query'], $param);
     }
    $componentFile=rareContext::getContext()->getComponentDir().trim($name,"/").".php";
    return rareView::render($param, $componentFile);
}
//参数合并,将
function rare_param_merge(){
     $numargs = func_num_args();
     $param=array();
     for($i=0;$i<$numargs;$i++){
        $_param=func_get_arg($i);
       if(is_string($_param))parse_str($_param,$_param);
       if(!is_array($_param))$_param=array();
       $param=array_merge($param,$_param);
      }
     return $param;
}
/**
 * 调用简单的helper 文件，文件如lib/helper/hello.php
 * @param $helper
 */
function use_helper($helper){
    static $helpers=array();
    if(in_array($helper, $helpers))return;
    $helperFile=rareContext::getContext()->getAppLibDir()."helper/".$helper.".php";
    if(!file_exists($helperFile)){
        $helperFile=rareContext::getContext()->getRootLibDir()."helper/".$helper.".php";
        if(!file_exists($helperFile))die("can not find helper ".$helper);
    }
    include $helperFile;
    $helpers[]=$helper;
}
//检查目录是否存在，不存在则创建
function directory($dir){
    return is_dir($dir) or (directory(dirname($dir)) and mkdir($dir, 0777));
}
/**
 * 返回json数据 
 * @param int $status 状态 建议0：失败 1：正常、成功
 * @param string $info  提示信息
 * @param mix $data   返回的数据,字符串或者数组
 */
function jsonReturn($status=1,$info="",$data=""){
  $json=array();
  $json['s']=$status;
  $json['i']=$info;
  $json['d']=$data;
  header("Content-Type:application/json");
  ob_clean();//clear output:Notice and others
  die(json_encode($json));
}
//字符串是否以指定值结尾
function str_endWith($str,$subStr){
    return substr($str, -(strlen($subStr)))==$subStr;
}
//字符串是否以指定值开始
function str_startWith($str,$subStr){
    return substr($str, 0,(strlen($subStr)))==$subStr;
}
//内部地址跳转
function forward($uri){
   rareContext::getContext()->executeActtion($uri);die;
 }
//客户端地址跳转 可以调用callBack函数进行跳转前的验证    
function redirect($url){
    if(!_rare_isUrl($url))$url=url($url);
    _rare_runHook('redirect', array($url));
    header("Location: ".$url);die;
}
//是否是https
function rare_isHttps(){
    return ( (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == 1)) ||
             (isset($_SERVER['HTTP_SSL_HTTPS']) && (strtolower($_SERVER['HTTP_SSL_HTTPS']) == 'on' || $_SERVER['HTTP_SSL_HTTPS'] == 1)) ||
             (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')
            );
}
//是否是ajax 请求
function rare_isXmlHttpRequest(){
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
}
//得到当前的url地址,并且可以添加其他的额外的参数
function rare_currentUri($param="",$full=false){
      $host="";
      if($full)$host=rare_httpHost();
      $uri=$_SERVER['REQUEST_URI'];
      if(!$param)return $host.$uri;
      $p=parse_url($uri);
      $param=rare_param_merge(isset($p['query'])?$p['query']:array(),$param);
      $param=http_build_query($param);
      return $host.($param?$p['path']."?".$param:$p['path']);
}

function rare_httpHost(){
    $host= 'http://'.$_SERVER['SERVER_NAME'];
    if(80 != $_SERVER['SERVER_PORT'] ){
        if(rare_isHttps()){
            $host = 'https://'.$_SERVER['SERVER_NAME'];
        }else{
            $host.=":".$_SERVER['SERVER_PORT'];
        }
    }
    return $host;
}

function _rare_isUrl($url){
  return str_startWith($url, "http://") || str_startWith($url, "https://") || str_startWith($url, "/");
}
//run user hook function if myHook class exist,it will run auto
function _rare_runHook($funName,$params){
  static $myHookExist=null;
  if($myHookExist===null)$myHookExist=class_exists("myHook",true);
  if($myHookExist && method_exists("myHook", $funName))call_user_func_array(array('myHook',$funName),$params);
}