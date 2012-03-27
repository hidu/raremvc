<?php
class loginAction extends rareAction{
   public function executeGet(){
      
   }
   
   public function executePost(){
      $user=$this->_postParam('user');
      $psw=$this->_postParam('psw');
      if(!empty($user) && $user != $psw ){
         rHtml::js_alertGo("login fail!\nuser neq psw!", -1);
       }
      $_SESSION['admin']=true;
      redirect("index");
   }
}