<?php
/**
 * 对memcache的一个简单封装
 *配置文件默认读取app的config目录下的memcache.php
 *若该配置文件不存在则读取根目录lib下的config/memcache.php
 *配置文件格式如下：
 *1.单服务器模式:
 *<?php
  $config['host']='10.10.10.1'; //服务器ip
  $config['port']=11211;        //端口
  $config['persistent']=1;      //是否长链接
  $config['lifetime']=3600;     //默认有效期
  $config['prefix']=100052;     //key前缀
  return $config;
  ?>
  
  2.多服务器模式
  <?php
  $first['host']='10.10.10.1'; //服务器ip
  $first['port']=11211;        //端口
  $first['persistent']=1;      //是否长链接

  $second['host']='10.10.10.2'; //服务器ip
  $second['port']=11211;        //端口
  $second['persistent']=1;      //是否长链接
  
  $config=array();
  $config['lifetime']=3600;     //默认有效期
  $config['prefix']=100052;     //key前缀 
  
  $config['servers']=array($first,$second);
  return $config;

 *@copyright rareMVC 
*@package addon\cache
 * @author duwei
**/
class rCache_memcache extends rCache{
  private $config;
  /**
   * @var Memcache
   */
  private $memcache;
  private $lifeTime=3600;    //默认有效期
  private $prefix="";//前缀
  
  private static  $instance;
  
  private  function __construct(){
      $this->init();
  }
  
  /**
   *@return rareCache_memcache
   */
  public static function getInstance(){
    if(!self::$instance)self::$instance=new self();
    return self::$instance;
  }
  
  private function init(){
       $this->config=rareConfig::getAll('memcache');
      if(!$this->config){
         $libConfigFile=rareContext::getContext()->getRootLibDir()."config/memcache.php";
         if(file_exists($libConfigFile)){
           $this->config=require $libConfigFile;
         }
       }
       if(!$this->config){
           throw new Exception('no config for memcache');
         }
       if(!isset($this->config['servers']))$this->config['servers'][]=$this->config;
       if(isset($this->config['lifetime']))$this->lifeTime=$this->config['lifetime'];
       if(isset($this->config['prefix']))$this->prefix=$this->config['prefix'];
        
       $this->memcache = new Memcache();
       $_fail_count=0;
       foreach ($this->config['servers'] as $server){
          $port = isset($server['port']) ? $server['port'] : 11211;
          if (!$this->memcache->addServer($server['host'], $port, isset($server['persistent']) ? $server['persistent'] :true)){
             trigger_error("connect memcache {$server['host']}:{$port} {$server['persistent']} fail",E_USER_ERROR);
             $_fail_count++;
          }
        }
       if($_fail_count==count($this->config['servers'])){
            throw new Exception("connect to the memcache server {$server['host']}:$port fail!");
        }
  }
  
  public function has($key){
     return  !(false === $this->memcache->get($this->prefix.$key));
  }
  
  public function get($key,$default=null){
     $value = $this->memcache->get($this->prefix.$key);
    return false === $value ? $default : $value;
  }
  
  public function remove($key){
    return $this->memcache->delete($this->prefix.$key);
  }
  
  public function set($key,$value,$life=null){
     $lifetime = is_null($lifetime) ? $this->lifeTime :$lifetime;
    return $this->memcache->set($this->prefix.$key, $value, false, $lifetime);
  }
  
  
  public function getBackend(){
    return $this->memcache;
  }
  
}