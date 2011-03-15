<?php
$config=array();
$config['title']['rule']=array(
                        rareValidate_type::Required,
                        rareValidate_type::Email,
                        );
$config['title']['msg']="请输入正确的标题！";                        
$config['title']['id']="title";                        

return $config;