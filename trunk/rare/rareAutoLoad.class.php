<?php
/**
 * 类自动装载
 * @example
 * include 'rareAutoLoad.php';
 * $option=array('dirs'=>'/www/lib/share/,/www/lib/api/',//class 从那些目录中查找
 *               'cache'=>'/tmp/111111.php',//class path 缓存文件
 *               ‘suffix’=>'.class.php'   //需要类自动装载的php类文件的后缀
 *                "hand"=>true,   //是否手动更新class 路径文件 ，为false 时 缓存文件写入到指定的cache文件中去，
 *                                      //为true 是需要手动允许 autoLoad.php 文件
 *                "rootDir"=>dirname(dirname(dirname(__FILE__))), //hand=true 时 需要定义 程序根目录,保证程序部署到服务器也能找到对于的class
 *                 "noCache"=>true   //不写缓存文件，默认为false,hand=true时可以方便程序开发。部署时建议修改为false
 *               );
 * rareAutoLoad::register($option);
 *
 * 参考了symfony 的类自动装载
 * 为了提供效率，将类的位置保存到缓存文件中，在第一次使用的时候会对dirs中的文件目录进行扫描
 * 需要自动装载的类的文件命名 要求 必须 以  .class.php 结束，如文件名为  a.class.php 中定义的类可以被扫描到而 a.php的文件会忽略掉
 * 类名 和 文件命名 可以没有关系  如 a.class.php 文件中 可以定义 class b{}
 *
 * @author duwei<duv123@gmail.com>
 *
 */
class rareAutoLoad
{
    private static $instance=null;
    private static $registered=false;
     
    private $cacheFile=null;
    private $classes=array();//对应class 类名 和对应文件路径
    private $option;
     
    private $hand=false;//是否手动运行该脚本进行class路径扫描
    /**
    * @param array $option 需要参数 dirs：扫描目录  cache：缓存文件
    */
    public function __construct($option){

        if(!isset($option['suffix'])) $option['suffix']=".class.php";//文件后缀
        $this->option=$option;
        if(!isset($option['cache'])){
            $trac=debug_backtrace(false);
            $calFile=$trac[2]['file'];
            $option['cache']="/tmp/rareautoLoad_".md5($calFile)."_".filemtime($calFile);
        }
        if(isset($option['hand']))$this->hand=(boolean)$option['hand'];
        $this->cacheFile=$option['cache'].".php";
        if(!isset($this->option['noCache']))$this->option['noCache']=false;
        $this->getClasses();
    }
     
    /**
     * 获取DAutoLoad 的单实例对象
     * @param array $option
     * @return DAutoLoad
     */
    private static function getInstance($option){
        if (!isset(self::$instance)){
            self::$instance = new rareAutoLoad($option);
        }
        return self::$instance;
    }

    /**
     * 注册自动装载
     * @param array $option   array('dirs'=>'/www/lib/share/,/www/lib/api/','cache'=>'/tmp/111111.php');
     * @throws Exception
     */
    public static function register($option) {
        if (self::$registered)return;
        // ini_set('unserialize_callback_func', 'spl_autoload_call');
        if (false === spl_autoload_register(array(self::getInstance($option), 'autoload'))){
            die(sprintf('Unable to register %s::autoload as an autoloading method.', get_class(self::getInstance())));
        }
        self::$registered = true;
    }

    /**
     * spl_autoload_call 调用 load class
     * @param $class
     * @return
     */
    public function autoload($class){
        if(class_exists($class, false) || interface_exists($class, false)) return true;
        if (isset($this->classes[$class])){
            $file=$this->classes[$class];
            if(isset($this->option['rootDir']) && $this->hand){
                $file=$this->option['rootDir'].$file;
            }
            require($file);
            return true;
        }else{
            $this->reload();
            if(isset($this->classes[$class])){
                require($this->classes[$class]);
                return true;
            }
        }
        return false;
    }
    /**
     * 获取类名列表
     * @return
     */
    private function getClasses(){
        if(file_exists($this->cacheFile)){
            $this->classes=require($this->cacheFile);
        }else{
            $this->reload();
        }
    }

    /**
     * 重新扫描一次
     * 并将类名的位置信息保存到cache 中
     * @return
     */
    private function reload(){
        if($this->hand && php_sapi_name()!="cli" && !$this->option['noCache'])return;
        if(!is_writable(dirname($this->cacheFile))) die('cache 不能写入');
        
        $this->classes=array();
        $dirs=$this->option['dirs'];
        if(!is_array($dirs)) $dirs=explode(",", $dirs);

        foreach($dirs as $dir){
            if(!$dir)continue;
            $this->scanDir($dir);
        }
        if($this->option['noCache'])return;
         
        $phpData="<?php\n/**\n*autoLoadCache\n*@since ".date('Y-m-d H:i:s')."\n*/\n";
        ksort($this->classes);
        $phpData.="return ".var_export($this->classes,true).";";
        file_put_contents($this->cacheFile, $phpData,LOCK_EX);
    }

    /**
     * 扫描文件夹以及文件
     * 只有 $this->option['suffix'] 命名的文件才会被扫描到
     * @param $dir
     * @return
     */
    private function scanDir($dir){
        $files=scandir($dir,1);
        foreach($files as $fileName){
            $file=rtrim($dir,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$fileName;
            if(is_dir($file) && strpos($fileName,'.')!==0){
                $this->scanDir($file);
            }else{
                if(strpos($fileName,$this->option['suffix'])){
                    preg_match_all('~^\s*(?:abstract\s+|final\s+)?(?:class|interface)\s+(\w+)~mi', file_get_contents($file), $classes);
                    if(isset($this->option['rootDir']) && $this->hand){
                            $file=substr(realpath($file),strlen($this->option['rootDir']));
                        }
                    foreach ($classes[1] as $class){
                        $this->classes[$class] = $file;
                       }
                }
            }
        }
    }
}

//cli 模式下 手动扫描lib 目录下的class 文件 参数为app 的路径
//php rareAutoLoad.class.php ../demo
//autoLoad 文件会写入到 ../demo/config/autoLoad.php 文件中
if(php_sapi_name()=="cli"){
    $appDir=$_SERVER['argv'][1];
    if(empty($appDir)){
     die('app dir need');
    }
    $appDir=realpath($appDir);
    if(!file_exists($appDir) || !is_dir($appDir)){
      die($appDir." not exist!");
    }
   
    $defaultConfigFile=$appDir."/config/default.php";
    $autoLoadFile=$appDir."/config/autoLoad.php";
    unlink($autoLoadFile);
    $option=array();
    if(file_exists($defaultConfigFile)){
      $defaultConfig=require $defaultConfigFile;
      $option=isset($defaultConfig['class_autoload_option'])?$defaultConfig['class_autoload_option']:array();
    }
    if(empty($option['dirs']))$option['dirs']=$appDir."/lib/";
    $option['cache']=$appDir."/config/autoLoad";
    date_default_timezone_set('Asia/Shanghai');
    rareAutoLoad::register($option);
    echo "class scan finish!\n";
}