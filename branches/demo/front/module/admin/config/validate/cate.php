<?php
$config=array();
$config['catename']=array(
    rValidate_type::Info=>array('label'=>'名称','msg'=>'请输入正确分类名称！'),
    rValidate_type::Required,
);
$config['pinyin']=array(
    rValidate_type::Info=>array('label'=>'拼音','msg'=>'请输入正确分类拼音！'),
    rValidate_type::Required,
    rValidate_type::Regex=>array("#[a-zA-Z0-1]+#"),
);
return $config;