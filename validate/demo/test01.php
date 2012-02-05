<?php
require '../validate.class.php';
$config=require 'rule/1.php';
$validate=new rValidate($config);

$data=array('email'=>'aaa','buyNum'=>'0','password'=>'1');

$validate->setField('password', "密码","请输入密码！");
$validate->setRule('password',rValidate_type::Required);
$validate->setRule('password',rValidate_type::Rangelength,array(6,20),"密码的长度限制在6-20位，您的密码长度是{length}!");

$validate->validate($data);
if($validate->isValidate()){
  echo "validate success";
}else{
  echo "fail\n";
  echo $validate->getErrorsAsString();
}
echo "\n";