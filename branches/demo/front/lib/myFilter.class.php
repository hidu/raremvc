<?php
/**
 *app default filter 
 */
class myFilter{
    private $context;
    //只会运行一次
   public function doFilter(){
       $this->context=rareContext::getContext();
       session_name('rare');
       session_start();
       service_sqlite::init();
       use_helper('common');
       
       $this->_checkAdminLogin();
       
   }
   private function _checkAdminLogin(){
     if($this->context->getModuleName()=='admin' && 
        $this->context->getActionName(true)!="admin/login" && 
        !isset($_SESSION['admin'])){
          redirect('admin/login');
     }
   }
   
   public function beforeExecute(){
      //任何action之前前都会运行，forward也生效
   }
}

