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
       $this->_checkCache();
       
       
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
   
   /**
    *进行动态内容的cache检查
    *演示 过滤器结合shutdown的使用 
    */
   private function _checkCache(){
       if($this->context->getModuleName()=='admin')return;
       if($_SERVER['REQUEST_METHOD']!="GET")return;
       register_shutdown_function(array("myFilter",'shutdowwn'));
       ob_start();
   }
   
   public function shutdowwn(){
       $html=ob_get_contents();
       ob_clean();
       
       $html=rHtml::reduceSpace($html);//去除html内容中多余的换行

       $md5=md5($html);
       rCache_etag::checkEtag($md5);
       echo $html;
   }
}

