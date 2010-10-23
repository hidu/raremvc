<?php
class loginCheckAction extends rareAction{
  public function execute(){
     $name=$_POST['name'];
     $psw=$_POST['psw'];
     if($name == "user" && $psw == 'passwd'){
         $data=array('status'=>1,'info'=>'恭喜你，帐号密码正确');
     }else{
         $data=array('status'=>0,'info'=>'帐号密码不正确');
     }
     echo json_encode($data);
  }
}