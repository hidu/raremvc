<?php
/**
 * @copyright rareMVC
 *空缓存对象 
 * @author duwei
*@package addon\cache
 */
class rCache_no extends rCache{
   public function has($key){
       return false;
   }
   
   public function get($key,$default=null){
      return null;
   }
   
   public function set($key,$data,$lifetime=null){
       return true;
   }
   
   public function remove($key){
       return true;
   }
}