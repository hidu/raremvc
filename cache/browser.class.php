<?php
class rCache_browser{
   protected static $lastTime=0;
   protected static $maxAge=0;
   
   public static function regirest($age=86400){
     self::$maxAge=$age;
     register_shutdown_function(array('rCache_browser','shutdown'));
   }
   
   public static function shutdown(){
       if(self::$maxAge<30)return;
  
       $files=get_included_files();
       foreach ($files as $file){
         $mtile=filemtime($file);
         if($mtile>self::$lastTime){
           self::$lastTime=$mtile;
         }
       }
      $last_modified=isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])?strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])+date("Z"):0;
      header('Last-Modified: ' . gmdate('D, d M Y H:i:s',self::$lastTime) . ' GMT');
      header("Expires: ".gmdate("D, d M Y H:i:s", time()+self::$maxAge)." GMT");
      header("Cache-Control: max-age=".self::$maxAge);
      if(self::$lastTime && $last_modified >= self::$lastTime){
         ob_clean();
         header("HTTP/1.1 304 Not Modified");
         exit;
       }
   }
  
   public static function setExpires($time){
      if(!is_numeric($time))$time=strtotime($time);
      if(self::$lastTime<$time){
         self::$lastTime=$time;
       }
   }
   public static function setMaxAge($age){
     self::$maxAge=$age;
   }
}