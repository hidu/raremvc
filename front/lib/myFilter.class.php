<?php
/**
 *app default filter 
 */
class myFilter{
    
    //只会运行一次
   public function doFilter(){
       session_start();
   }
   public function beforeExecute(){
      //任何action之前前都会运行，forward也生效
   }
}

