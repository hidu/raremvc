<?php
/**
 * rare php framework
 * http://raremvc.googlecode.com
 * http://rare.hongtao3.com
 * 
 * @package rare
 * @author duwei duv123@gmail.com
 * @version 1.2
 */

/**
 * app 入口类
 */
!defined("PROD") && define("PROD",0);                //是否是生产环境默认为否,对自定义错误页面等会有影响
final class rareContext{

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
    private $version='1.2 20120606';                 //当前框架版本
    private $cacheDir="";                            //cache目录
    private $filter=null;                            //过滤器
    private $suffix;                                 //地址后缀        
    private $responseCode=200;                       //http response code                 
    
    private static $instance;                         //app实例    
        
    public function __construct($appDir){
        $this->appDir=$appDir;
        $this->appName=basename($this->appDir);
        $this->rootDir=dirname($this->appDir)."/";
        define("RARE_APP_NAME", $this->appName);       
        define("RARE_ROOT_DIR", $this->rootDir);      //预定义 程序根目录
        define("RARE_APP_DIR",  $this->appDir);       //预定义 app根目录
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
        $this->regAutoLoad();
        $this->parseRequest();
        $this->executeFilter("doFilter");
        $this->executeActtion($this->uri);
    }
    
    private function init(){
        date_default_timezone_set(rareConfig::get('timezone','Asia/Shanghai'));
        rareConfig::set('charset', rareConfig::get('charset','utf-8'));
        header("Content-Type:text/html; charset=".rareConfig::get('charset'));
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
                                   'cache'=>$this->getCacheDir()."classAutoLoad"
                                   );
            $autoLoadConfigFile=$this->getConfigDir()."autoLoad.php";
            if(file_exists($autoLoadConfigFile)){
               $_autoloadOption['hand']=true;
               $_autoloadOption['cache']=$this->getConfigDir()."autoLoad";
              }                                     
            $option=array_merge($_autoloadOption,rareConfig::get("class_autoload_option",array()));
            if(isset($option['dir_more'])){
              $option['dirs'].=",".$option['dir_more'];
              }
            rareAutoLoad::register($option);
        }
    }
    //解析url地址
    private function parseRequest(){
        $requestUri=$_SERVER['REQUEST_URI'];
         ///---------------------------------------------------------------
         //get and check the suffix
        $tmp=parse_url($requestUri);
        preg_match("/\.(.*)$/", $tmp['path'],$matches);
        $this->suffix=($matches && isset($matches[1]))?$matches[1]:null;
        rare_go404If($this->suffix==='');
        $suffix_accept=rareConfig::get('suffix_accept',null);
        if($suffix_accept){
            rare_go404If(!in_array($this->suffix, $suffix_accept));
         }
         //==================================================================
        
        $this->webRoot=rareConfig::get("webroot",substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],"/")+1));
        $pathInfo=pathinfo($_SERVER['SCRIPT_NAME']);
        $this->scriptName=$pathInfo["basename"];
        $this->webRootUrl=rare_httpHost().$this->webRoot;

          
        $this->uri=trim(substr($requestUri, strlen($this->webRoot)),"/");
        $scriptNamelen=strlen($this->scriptName);
        if(substr($this->uri, 0,$scriptNamelen)==$this->scriptName){
            $this->uri=substr($this->uri, strlen($this->scriptName)+1);
            $this->isScriptNameInUrl=true;
        }
        $this->uri=$this->uri?$this->uri:"index/index";
        /**
         * 添加过滤器beforeRouter($uri)的方法，以支持在该过滤器中对url地址进行预解析
         */
        $this->executeFilter('beforeRouter',array(&$this->uri));
        
        include_once dirname(__FILE__).'/rareRouter.class.php';
        rareRouter::init();
        $this->uri=rareRouter::parse($this->uri);
        $uriInfo=$this->parseActionUri($this->uri);
        $this->moduleName=$uriInfo['m'];
        $this->actionName=$uriInfo['a'];
    }
    
    public function error404(){
        @header($_SERVER["SERVER_PROTOCOL"].' 404');
         $this->setResponseCode(404);
         $this->goError(404);      
         $this->_errorPage("404 Not Found","The requested URL <b>{$_SERVER['REQUEST_URI']}</b> was not found on this server.");
     }
    //500错误
    public function error500($_error=array()){
        @header($_SERVER["SERVER_PROTOCOL"].' 500');
        $this->setResponseCode(500);
         if(PROD){
           $this->goError(500);
           $this->_errorPage("500 Internal Server Error", "");
         }else{
             $this->_errorPage("500 Internal Server Error", nl2br($_error['message'])." in file ".$_error['file']." at line ".$_error['line']);
         }      
    }
    private function _errorPage($title,$msg){
        ob_end_clean();
        @ob_clean();
        $html="<!DOCTYPE html><html><head><meta http-equiv='content-type' content='text/html;charset=".rareConfig::get('charset')."'>".
               "<title>{$title}</title></head><body><p style='background:#3366cc;color:white;padding:5px;font-weight:bold;'>Error</p>".
               "<h1>{$title}</h1><div  style='color:#000'>{$msg}</div><br/><br/><a href='".public_path("")."'>Go Home</a><p style='background:#3366cc;height:4px'>&nbsp;</p></body></html>";
       echo $html;
       die();
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
            if(rare_isUrl($errorUri)){
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
     
     protected  function getActionFile($moduleName,$actionName){
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
        
        chdir(dirname($actionFile));
            
        include($actionFile);
        $actionClass=$this->actionName."Action";
        if(class_exists($this->moduleName.'_'.$actionClass,false)){
           $actionClass=$this->moduleName.'_'.$actionClass;
        }
        
        $action = new $actionClass($this->moduleName,$this->actionName);
        
        $this->executeFilter("beforeExecute",array($action));//执行具体动作前执行过滤器指定方法

        $action->preExecute();

        //自定义action方法 若需要使用，可以在config/default.php中定义 $config['rest']='method';
        //如上定义自定义函数名称通过$_REQUEST['method']来确定，如method=delte 会运行executeDelete方法
        $restMethod=rareConfig::get('rest');
        $customFn=false;
        if($restMethod){
            if(!is_string($restMethod))$restMethod="method";
             if(isset($_REQUEST[$restMethod]) && $_REQUEST[$restMethod]){
              $customFn="execute".ucfirst(strtolower($_REQUEST[$restMethod]));
             }
          }
        $request_method=strtolower($_SERVER["REQUEST_METHOD"]);
        $restFn="execute".ucfirst($request_method);

        //fix head method request as get if not define the head mehtod in the action
        if($request_method=="head" && !method_exists($action, $restFn)){
          $restFn="executeGet";
         }
        if($customFn && method_exists($action, $customFn)){
            $result=call_user_func(array($action,$customFn));
        }elseif(method_exists($action, $restFn)){
            $result=call_user_func(array($action,$restFn));
         }elseif(method_exists($action, 'execute')){
           $result=$action->execute();
         }else{
           $this->error404();
          }
        if(($result!==null && empty($result)))return;
        $action->display($result);
    }
    /**
     * 将当前的uri解析为action 方便识别的数组格式
     * @param string $uri 如index/demo?a=1&b=2
     * @return array
     */
    public function parseActionUri($uri){
        //This function  parse_url doesn't work with relative URLs. 
         //所以当 $uri=index/demo?url=http://example.com/hello.php?id=2  的时候会出错 补全为完整的地址可以避免错误
        $tmp=parse_url("http://127.0.0.1/".$uri);
        if(empty($tmp['path']))$tmp['path']='index';
        $tmp['path']=preg_replace("/\.\w*$/", "", $tmp['path']);
        $path=explode("/",trim($tmp['path'],"/"));
        if(empty($path[1]))$path[1]='index';
        $uriInfo=array();
        $uriInfo['m']=$path[0]=="~"?$this->moduleName:$path[0];
        $uriInfo['a']=$path[1]=="~"?$this->actionName:$path[1];
        $uriInfo['q']=empty($tmp['query'])?"":$tmp['query'];
        $uriInfo["u"]=$uriInfo['m']."/".$uriInfo['a'];
        return $uriInfo;
    }
    //执行过滤器
    private function executeFilter($method='',$params=array()){
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
              call_user_func_array(array($this->filter,$method), $params);
        }
    }
    
    /**
     *返回当前app所在的根目录
     * @return string
     */
    public function getAppDir(){
        return $this->appDir;
    }
    
    /**
     * 返回当前完整程序的更目录
     * @return string
     */
    public function getRootDir(){
         return $this->rootDir;
    }
    /**
     *获取当前app的名称 比如demo 
     */
    public function getAppName(){
      return $this->appName;
    }
    public function getAppLibDir(){
        return $this->getAppDir()."lib/";
    }
    public function getRootLibDir(){
         return $this->rootDir."lib/";
    }
    
    /**
     * 获取当前的模块名称
     * @return string
     */
    public function getModuleName(){
        return $this->moduleName;
    }
   
    /**
     * 返回当前动作名称
     * 如返回  login 或者 user/login
     * @param boolean $full 是否包括模块名称 默认为false
     * @return string
     */
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
    /**
     *返回入口文件的名称 如 index.php test.php 
     *@return string 
     */
    public function getScriptName(){
        return $this->scriptName;
    }
    /**
     * url中是否包含脚本名称 如/rare/index.php/demo 为true
     */
    public function isScriptNameInUrl(){
        return $this->isScriptNameInUrl;
    }

    public function getConfigDir(){
        return $this->getAppDir()."config/";
    }
    
    public function getCacheDir(){
        return rareConfig::get("cache_dir");
    }
    
    /**
     *返回当前的请求地址，并非浏览器中的地址。如 
     *index/index 
     *index
     *?test=2
     *index/demo?test=2
     * @return string
     */
    public function getRequestUri(){
        return $this->uri;
    }
    /**
     *返回当前地址的 后缀 如 html
     */
    public function getSuffix(){
      return $this->suffix;
    }
    /**
     *返回框架所在目录 
     */
    public function getFrameworkDir(){
      return dirname(__FILE__)."/";
    }
    
    public function setResponseCode($code){
      $this->responseCode=$code;
    }
    
    public function getResponseCode(){
      return $this->responseCode;
    }
    
}

/**
 * 模板相关操作
 */
final class rareView{
    private static $slots=array();
    
    /**
     * 将数据渲染到模板中去
     * @param array $vars
     * @param string $viewFile 模板文件路径
     */
    public static function render($vars,$viewFilePath){
        $viewFile=realpath($viewFilePath);
        if(!$viewFile)$viewFile=$viewFilePath;
        $rare_currentPWD = getcwd();
        chdir(dirname($viewFile));
        if(is_string($vars))parse_str($vars,$vars);
        $vars['rare_vars']=$vars;
        if(is_array($vars))extract($vars);
        ob_start();
        include $viewFile;
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
        rareConfig::set('title', trim($title,"-"));
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
     * 移除已经添加的css文件
     * @param string $css
     */
    public static function removeCss($css){
         self::_staticIndex("css", $css,-1);
    }
    /**
     * 移除添加的js文件
     * @param string $js
     */
    public static function removeJs($js){
         self::_staticIndex("js", $css,-1);
    }
    /**
     * 添加一个js,css 文件到head 标签中 或者 删除 index<0为删除
     * @param string $type 
     * @param string $uri
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
               if($index>0){
                  $_tmp=array_slice($tmp, 0,$index);
                  $_tmp[]=$uri;
                  $tmp=array_merge($_tmp,array_slice($tmp, $index));
                }else{
                  $uri=str_replace("\*",".+",preg_quote($uri));
                  foreach ($tmp as $_i=>$_uri){
                    if(preg_match("#^".$uri."$#", $_uri)){
                       unset($tmp[$_i]);
                       }
                    }   
                  }
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
         if(!function_exists("_rare_fill_url")){
            function _rare_fill_url($_uris){
                if(is_string($_uris))$_uris=explode(",", $_uris);
                $tmp=array();
                foreach($_uris as $_uri){
                  $_uri=trim($_uri);
                  if(!$_uri)continue;
                  $tmp[]=rare_isUrl($_uri)?$_uri:public_path($_uri);
                }
                return array_unique($tmp);
            }
         }
        $csss=_rare_fill_url(rareConfig::get("css",array()));
        _rare_runHook("css", array(&$csss));
        $cssVersion=rareConfig::get("cssVersion",null);
        foreach ($csss as $css){
            $css.=$cssVersion?(strpos($css, "?")?"&":"?")."v=".$cssVersion.".css":"";
            echo "<link rel=\"stylesheet\" href=\"{$css}\" type=\"text/css\" media=\"all\" />\n";
        }
         
        $jss=_rare_fill_url(rareConfig::get("js",array()));
        _rare_runHook('js', array(&$jss));
        $jsVersion=rareConfig::get("jsVersion",null);
        foreach ($jss as $js){
            $js.=$jsVersion?(strpos($js, "?")?"&":"?")."v=".$jsVersion.".js":"";
            echo "<script type=\"text/javascript\" src=\"{$js}\"></script>\n";
        }
    }
    
    /**
     *输出title和meta标签 
     */
    public static function include_title(){
        echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".rareConfig::get('charset')."\" />\n";
        echo "<title>".htmlspecialchars(rareConfig::get("title","rare app"))."</title>\n";
       foreach (rareConfig::getAll() as $key=>$value){
           if(preg_match("/^meta\.(.+)$/", $key,$matches)){
               echo "<meta name=\"$matches[1]\" content=\"".htmlspecialchars($value)."\" />\n";
             }
         }
    }
     /**
      * 设置meta 的关键词
      * @param string $keywords
      */
    public static function setMeta_keywords($keywords){
        rareConfig::set('meta.keywords', $keywords);   
     }
     /**
      * 设置meta的描述
      * @param string $description
      */
    public static function setMeta_description($description){
        rareConfig::set('meta.description', $description);   
     }
     /**
      *设置slot的值 
      * @param string $name
      * @param string $value
      * @param int $mod  -1：前置 0:替换  1：追加
      */
     public static function slot_set($name,$value,$mod=0){
       if(1==$mod){
         $value=self::slot_get($name).$value;
       }else if(-1==$mod){
         $value=$value.self::slot_get($name);
        }
       self::$slots[$name]=$value;
     }
     
     public static function slot_has($name){
       return isset(self::$slots[$name]);
     }
     
     public static function slot_get($name,$default=null){
       return self::slot_has($name)?self::$slots[$name]:$default;
     }
     
     private static $_slot_keys_cache=array();
     
     public static function slot_start($name){
       array_push(self::$_slot_keys_cache,$name);
       ob_start();
       ob_implicit_flush(0);
     }
     
     public static function slot_end(){
       $data = ob_get_clean();
       $name=array_pop(self::$_slot_keys_cache);
       self::slot_set($name, $data);
     }
     
}

/**
 *动作类
 */
abstract class rareAction{
    /**
     * @var rareContext
     */
    protected  $context;
    protected  $layout=null;
    protected  $layoutForce=false;//是否强制在任何情况下都使用layout,主要是针对在ajax方式调用是仍然需要使用layout的情况
    public     $vars;             //赋值给模板的所有的变量   
    protected  $viewFile;
    protected  $moduleName;
    protected  $actionName;
    protected  $isRender=false;  //是否进行过模板渲染,display方法调用后设置为true

    public function __construct($moduleName,$actionName){
        $this->context=rareContext::getContext();
        $this->moduleName=$moduleName;
        $this->actionName=$actionName;
        $this->viewFile=$this->context->getModuleDir().$moduleName."/view/".$actionName.".php";
    }
    
    public function getModuleName(){
      return $this->moduleName;
    }
    public function getActionName($full=false){
       return $full?$this->moduleName."/".$this->actionName:$this->actionName;
    }
    /**
     * 获取request值
     * @param string $key  变量名称
     * @param string $default  默认值
     */
    public function getRequestParam($key,$default=null){
        return empty($_REQUEST[$key])?$default:$_REQUEST[$key];
     }
    public function getGetParam($key,$default=null){
        return $this->_getParam($key,$default);
     }
     
    public function getPostParam($key,$default=null){
        return $this->_postParam($key,$default);
     }
     
    public function _getParam($key,$default=null){
        return empty($_GET[$key])?$default:$_GET[$key];
     }
    public function _postParam($key,$default=null){
        return empty($_POST[$key])?$default:$_POST[$key];
     }
     /**
      * 将变量赋值给模板
      * @param string $key
      * @param object $value
      */
    public function assign($key,$value=null){
       if(is_array($key)){
          foreach ($key as $_k=>$_v){
            $this->vars[$_k]=$_v;
            }
        }else{
           $this->vars[$key]=$value;
        }
     }
    /**
     *execute 前执行的方法
     */
    public function preExecute(){}
    /**
     *动作的入口
     */
//    abstract function execute();
     
   
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
    protected   function getLayoutFile(){
        if(!$this->layoutForce && (false===$this->layout || rare_isAjax()))return null;
        if(null==$this->layout){
            $layoutFile=$this->context->getLayoutDir().$this->context->getModuleName().".php";
            if(!file_exists($layoutFile)){
                $layoutFile=$this->context->getLayoutDir()."default.php";
              }
        }else{
          if(rare_strStartWith($this->layout, ":")){  //用于适配一个自定义的布局文件的路径
            $layoutFile=substr($this->layout, 1);
          }else{
            $layoutFile=$this->context->getLayoutDir().$this->layout.".php";
           }
        }
        return $layoutFile;
    }

    /**
     * 渲染模板
     * 若$viewFile以:开头，这将：后面的内容作为body的内容。
     * @param $viewFile
     */
    public function display($viewFile=null){
        if($this->isRender)return;
        $this->isRender=true;
        if($viewFile && rare_strStartWith($viewFile, ":")){
          $body=substr($viewFile, 1);
        }else{
          $body=$this->fetch($viewFile);
        }
        $layoutFile=$this->getLayoutFile();
        _rare_runHook("display", array(&$body,&$layoutFile));
        if($layoutFile){
            chdir(dirname($layoutFile));
            include($layoutFile);
        }else{
            echo $body;
        }
    }
    
    /**
     *渲染指定模版 并返回内容 
     * @param $viewFile
     */
    public function fetch($viewFile=null){
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
      return rareView::render($this->vars, $this->viewFile);
     }
}
/**
 * 配置操作类，设置、读者指定的配置文件
 * @author duwei
 */
final class rareConfig{
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
            $config=self::requireFile($file);
        }
        self::$configs[$configName]=$config;
        return $config;
    }
    public static function requireFile($file){
       if(file_exists($file)){
            return require $file;
        }
        return null;
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
     if(is_bool($suffix)){
       $full=$suffix;
       $suffix='';
     }
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
    $suffix=($suffix && $uri && !rare_strEndWith($uri, "/"))?(".".$suffix):"";
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
 * 组件的位置在 app/component/  或者 /lib/component/
 * @param string $name  组件名称
 * @param array $param  参数
 */
function fetch($name,$param=null){
    $tmp=parse_url($name);
    $name=trim($tmp['path'],"/");
    if(isset($tmp['query'])){
       $param=rare_param_merge($tmp['query'], $param);
     }
     /**
      *若myHook::fetch 有返回值，则 直接返回返回值
      *用处：如不修改之前的代码 实现 组件添加缓存功能 可以和 下面的 myHook::fetch_render 结合使用
      */
    $result=_rare_runHook('fetch', array($name,$param));
    if($result !=null)return $result;
     
    $componentFile=rareContext::getContext()->getComponentDir().$name.".php";
    if(!file_exists($componentFile)){
      $componentFile=rareContext::getContext()->getRootLibDir()."component/".trim($name,"/").".php";
    }
    $html=rareView::render($param, $componentFile);
    
    $result=_rare_runHook('fetch_render', array($name,$param,$html));
    if($result !=null)$html=$result;
    
    return $html;
}
/**
 *参数合并 
 *@example
 *  $a="a=1&b=2&c=3";
 *  $b=array('d'=>4,'a'=>'aaa');
 *  $c=rare_param_merge($a,$b);
 *  ---->
 *  $c=array('a'=>'aaa',$b=>'2','c'=>'3','d'=>4);
 */
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
    $helperNames=explode(",", $helper);
    foreach ($helperNames as $helper){
      $helper=trim($helper);
      if(empty($helper) || in_array($helper, $helpers))return;
      $helperFile=rareContext::getContext()->getAppLibDir()."helper/".$helper.".php";
      if(!file_exists($helperFile)){
          $helperFile=rareContext::getContext()->getRootLibDir()."helper/".$helper.".php";
          if(!file_exists($helperFile))die("can not find helper ".$helper);
      }
      include $helperFile;
      $helpers[]=$helper;
    }
    unset($helperNames);
}
/**
 * 检查目录是否存在，不存在则创建
 * @param string $dir
 */
function rare_mkdir($dir){
    return is_dir($dir) or (rare_mkdir(dirname($dir)) and mkdir($dir, 0777));
}
/**
 * 返回json数据 
 * @param int $status 状态 建议0：失败 1：正常、成功
 * @param string $info  提示信息
 * @param mix $data   返回的数据,字符串或者数组 
 * @param boolean $header  返回结果是否修改header 的content-type 当使用ajaxFile上传文件时(iframe方式),标记为false 
 */
function jsonReturn($status=1,$info="",$data="",$header=true){
  $json=array();
  $json['s']=$status;
  $json['i']=$info;
  $json['d']=$data;
  if($header && rareConfig::get('json_header',true)){
     header("Content-Type:application/json;charset=".rareConfig::get('charset'));
  }
  ob_clean();//clear output:Notice and others
  echo json_encode($json);
  die();
}
/**
 * 字符串是否以指定值结尾
 * @param string $str
 * @param string $subStr
 */
function rare_strEndWith($str,$subStr){
    return strcmp(substr($str, -(strlen($subStr))),$subStr)==0;
}
//
/**
 * 字符串是否以指定值开始
 * @param string $str
 * @param string $subStr
 */
function rare_strStartWith($str,$subStr){
    return strcmp(substr($str, 0,(strlen($subStr))),$subStr)==0;
}
/**
 * 内部地址跳转,客户端的地址不变
 * 如forward("user/login?t=".time()) 参数t可以在 $_GET中获取到
 * @param string $uri
 */
function forward($uri){
   rareContext::getContext()->executeActtion($uri);die;
 }
/**
 * 
 * 客户端地址跳转 可以调用callBack函数进行跳转前的验证 
 * redirect("http://www.hongtao3.com/404.html");
 * redirect("index/login?t=".time());
 * @param string $url
 */   
function redirect($url){
    if(!rare_isUrl($url))$url=url($url);
    _rare_runHook('redirect', array($url));
    rareContext::getContext()->setResponseCode(302);
    header("Location: ".$url);die;
}
/**
 * 是否是https
 * @return boolean
 */
function rare_isHttps(){
    return ( (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == 1)) ||
             (isset($_SERVER['HTTP_SSL_HTTPS']) && (strtolower($_SERVER['HTTP_SSL_HTTPS']) == 'on' || $_SERVER['HTTP_SSL_HTTPS'] == 1)) ||
             (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')
            );
}
/**
 *是否是ajax 请求 
 */
function rare_isAjax(){
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
}
/**
 *得到当前的url地址,并且可以添加其他的额外的参数 
 * @param string $param
 * @param boolean $full
 * 当前地址                                                                    结果                     
 * /      				==>  rare_currentUri("a=1") 		       ==>	 /?a=1
 * /?a=2    			==>  rare_currentUri("a=1") 		       ==> /?a=1
 * /?a=3    			==>  rare_currentUri("a=1&b=2",true)    	==> http://www.hongtao3.com/?a=1&b=2
 */
function rare_currentUri($param="",$full=false){
      $host="";
      if($full)$host=rare_httpHost();
      $uri=$_SERVER['REQUEST_URI'];
      if(!$param)return $host.$uri;
      $p=parse_url($uri);
      $param=rare_param_merge(isset($p['query'])?$p['query']:array(),$param);
      foreach ($param as $_k=>$_v){
        if(!strlen($_v))unset($param[$_k]);
      }
      $param=http_build_query($param);
      return $host.($param?$p['path']."?".$param:$p['path']);
}

/**
 * 返回主机根目录地址 如 
 * http://www.hongtao3.com 
 * http://www.hongtao3.com:81
 * https://www.hongtao3.com 
 * https://www.hongtao3.com:444
 */
function rare_httpHost(){
    return (rare_isHttps()?'https://':"http://").$_SERVER['HTTP_HOST'];
}
/**
 * 判断是否是url地址 如
 * http://www.hongtao3.com 
 * https://www.hongtao3.com  
 * /rare/demo.html 
 * 均为真
 * @param string $url
 */
function rare_isUrl($url){
  return rare_strStartWith($url, "http://") || rare_strStartWith($url, "https://") || rare_strStartWith($url, "/");
}

//run user hook function if myHook class exist,it will run auto
function _rare_runHook($funName,$params){
  if(class_exists("myHook",true) && method_exists("myHook", $funName))
   return call_user_func_array(array('myHook',$funName),$params);
}

/**
 * 根据条件判断是否需要跳转到404页面
 * @param boolean $condition
 */
function rare_go404If($condition=true){
  if($condition)rareContext::getContext()->error404();
}

/**
 * 包含指定的文件 可以用在视图文件中包含 子文件,能够对局部变量进行有效的隔离
 * 该方法是对组件 fetch 的一个补充
 * @example 
 *   1. rare_include("sub/header.php");
 *   2. rare_include("sub/header.php?step=1");
 *   3. rare_include("sub/header.php","step=1");
 *   4. rare_include("sub/header.php",array('step'=>1,'ids'=>array(1,2,3)));
 * @param string $filePath
 * @param string|array $params 附带的参数 如 a=1&b=2 或者array('a'=>1,'b'=>2)
 * @param boolean $return 是否将内容返回
 */
function rare_include($filePath,$params=null,$return=false){
   $tmp=parse_url($filePath);
   $filePath=$tmp['path'];
   if(isset($tmp['query'])){
      $params=rare_param_merge($tmp['query'], $params);
   }
  if(!is_array($params))parse_str($params,$params);
  $html=rareView::render($params, $filePath);
  if($return)return $html; 
  echo $html; 
}

function slot_get($name,$default=''){
   return rareView::slot_get($name,$default);
}

function slot_has($name){
  return rareView::slot_has($name);
}

function slot_start($name){
   return rareView::slot_start($name);
} 

function slot_end(){
  return rareView::slot_end();
}

/**
 * 
 * @param string $name
 * @param string $value
 * @param int $mod 0:替换  1：追加 -1：前置
 * @see rareView::slot_set
 */
function slot_set($name,$value,$mod=0){
  return rareView::slot_set($name, $value);
}