<?php
/**
 *对象缓存，缓存的数据会自动进行虚拟化处理、取出时进行反虚拟化
 *@copyright rareMVC 
 *@author duwei
 *@package addon\cache
 */
class rCache_object extends rCache{
   /**
    * @var rCache
    */
   private $handle;
   
    public function __construct($dbName='cache',$cacheMod='app'){
      if(in_array('sqlite', pdo_drivers())){
         $this->handle=new rCache_sqlite($dbName,$cacheMod);
      }else{
        $this->handle=new rCache_file($dbName,$cacheMod);
      }
     }
     
    public function setHandle($handle){
       $this->handle=$handle;
     }
    
    
    public function has($key){
      return $this->handle->has($key);
    }
    
   public function get($key,$default=null){
     if($this->handle->has($key)){
        $data=$this->handle->get($key);
        return unserialize($data);
     }else{
        return $default;
     }
   }
   
   public function set($key,$data,$lifetime=0){
      return $this->handle->set($key, serialize($data),$lifetime);
   }
   
   public function remove($key){
     $this->handle->remove($key);
   }
}