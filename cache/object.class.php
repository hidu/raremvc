<?php
/**
 *@copyright rareMVC 
 *@author duwei
 *对象缓存，缓存的数据会自动进行虚拟化处理、取出时进行反虚拟化
 */
class rCache_object extends rCache{
   /**
    * @var rCache
    */
   private $handle;
   
    public function __construct(){
       $this->handle=new rCache_sqlite("object");
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