<?php
/**
 *@copyright rareMVC 
 *@author duwei
 *
 */
abstract class rCache{
   abstract public function has($key);
   abstract public function get($key,$default=null);
   abstract public function set($key,$data,$lifetime=null);
   abstract public function remove($key);
   
   protected function getCacheDirByMod($cacheMod){
       if( $cacheMod == 'app'){
           return RARE_CACHE_DIR;
       }elseif( $cacheMod == 'root' ){
           return dirname(RARE_CACHE_DIR)."/";
       }else{
           return $cacheMod."/";
       }
   }
}
