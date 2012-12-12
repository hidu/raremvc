<?php
/**
 * 
 *@author duwei
 *@package addon\cache
 */

class rCache_browser{
   protected static $lastTime=0;
   protected static $maxAge=0;
   
   public static function regirest($maxAge=2){
     self::$maxAge=$maxAge;
     register_shutdown_function(array('rCache_browser','shutdown'));
   }
   
   public static function shutdown(){
      $last_modified=isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])?strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])+date("Z"):0;
      header('Last-Modified: ' . gmdate('D, d M Y H:i:s',self::$lastTime) . ' GMT');
      if(self::$maxAge>0){
//        header("Expires: ".gmdate("D, d M Y H:i:s", time()+self::$maxAge)." GMT");
        header("Cache-Control: max-age=".self::$maxAge);
      }
      if(self::$lastTime && $last_modified >= self::$lastTime){
         ob_clean();
         header("HTTP/1.1 304 Not Modified");
         exit;
       }
   }
  
   public static function setContentDate($time){
      if(!is_numeric($time))$time=strtotime($time);
      if(self::$lastTime<$time){
         self::$lastTime=$time;
       }
   }
   public static function setMaxAge($age){
     self::$maxAge=$age;
   }
}