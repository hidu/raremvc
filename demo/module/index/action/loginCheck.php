<?php
class loginCheckAction extends rareAction{
  public function execute(){
     $name=$_POST['name'];
     $psw=$_POST['psw'];
     echo $_GET['ccc'];
     if($name == "user" && $psw == 'passwd'){
         jsonReturn(1,"恭喜你，帐号密码正确");
     }else{
         jsonReturn(0,"帐号密码不正确","<font color='red'>帐号密码不正确</font>");
     }
  }
}