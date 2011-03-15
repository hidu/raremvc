<?php
/**
 * 对memcache的一个简单封装
 *@copyright rareMVC 
 * @author duwei
 *
 */
class rareCache_memcache extends rareCache{
  private $config;
  /**
   * @var Memcache
   */
  private $memcache;
  private $lifeTime=3600;    //默认有效期
  private $prefix;//前缀
  
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
       
       $this->memcache = new Memcache();
       foreach ($this->config['servers'] as $server){
          $port = isset($server['port']) ? $server['port'] : 11211;
          if (!$this->memcache->addServer($server['host'], $port, isset($server['persistent']) ? $server['persistent'] :true)){
            throw new Exception("connect to the memcache server {$server['host']}:$port fail!");
          }
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