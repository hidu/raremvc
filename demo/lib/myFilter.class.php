<?php
class myFilter{
   public function __construct($context){
   }
   
   public function debug_dump(){
    dump($_SERVER);
   }
   public function debug_dump1(){
    $error=error_get_last();
    dump($error);
   }
}