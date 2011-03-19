<?php
/**
 *在一次请求过程中的上下文缓存 
 * @author duwei
 *
 */
class rCache_request extends rCache{
      private $cacheData=array();
      private static $init=null;
   
     /**
      * 获取一个实例
      * @return rCache
      */
     public static function getInstance()
     {
       if(!self::$init)
       {
         self::$init=new self();
       }
       return self::$init;
     }
     
     /**
      * 获取全部
      * @return array
      */
     public function  getAll()
     {
       return $this->cacheData;
     }
     /**
      * 添加、设置cache
      * @param mixed $key
      * @param mixed $value
      * @param int $time
      * @return object
      */
     public function set($key,$value,$time=0)
     {
       if(count($this->cacheData)>1000)
       {
         array_shift($this->cacheData);
       }
        $this->cacheData[$key]=$value;
     }
     
     /**
      * 判断是否有
      * @param mixed $key
      * @return boolean
      */
     public function has($key)
     {
        return array_key_exists($key,$this->cacheData);
     }
     
     /**
      * 取数据
      * @param mixed $key
      * @return mixed
      */
     public function get($key,$default=null)
     {
        return $this->cacheData[$key];
     }
     
   /**
    * 清除所有的数据
    * @return boolean
    */
    public function removeAll()
    {
       $this->cacheData=array();
     }
     
     /**
      * 清除一个cache
      * @param unknown_type $key
      * @return unknown_type
      */
     public function remove($key)
     {
        unset($this->cacheData[$key]);
     }
}