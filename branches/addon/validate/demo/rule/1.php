<?php
$config=array();
$config['title']=array(
    rValidate_type::Info=>array('label'=>'标题','msg'=>'请输入有效的{label}！'),
    rValidate_type::Required,
);
$config['email']=array(
   rValidate_type::Info=>array('label'=>'邮箱','msg'=>'请输入邮箱'),
   rValidate_type::Required,
   rValidate_type::Email,
);
$config['buyNum']=array(
   rValidate_type::Info=>array('label'=>'购买数量','msg'=>'请输入{label}'),
   rValidate_type::Required,
   rValidate_type::Number,
   rValidate_type::Range=>array(array(1,20),"请输入有效的{label},您现在输入的是{value}"),
);
return $config;