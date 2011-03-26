<?php
$config=array();
$config['title']['rule']=array(
                        rValidate_type::Required,
                        rValidate_type::Email,
                        rValidate_type::SmallThan=>'ttt'
                        );
$config['title']['msg']="请输入正确的标题！";                        
return $config;