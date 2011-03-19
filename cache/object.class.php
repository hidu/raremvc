<?php
/**
 *@copyright rareMVC 
 *@author duwei
 *对象缓存，缓存的数据会自动进行虚拟化处理、取出时进行反虚拟化
 */
class rCache_object extends rCache_file{
    public function __construct(){
       parent::__construct();
    }
    
   public function get($key,$default=null){
      $file=$this->getCacheFilePath($key);
      if(!file_exists($file))return $default;
      $data=parent::get($key);
      $data=unserialize($data);
      return $data===false?$default:$data;
   }
   
   public function set($key,$data,$lifetime=0){
      return parent::set($key, serialize($data),$lifetime);
   }
}