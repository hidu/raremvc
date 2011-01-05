<?php
class myFilter{
   
   public function doFilter(){
   }
   
   public function debug_dump(){
    dump($_SERVER);
   }
   public function debug_dump1(){
    $error=error_get_last();
    dump($error);
   }
   
   public function beforeExecute(){
      echo time();
   }
}