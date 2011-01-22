<?php
$config=array();
$config['index/index'][]=array(
                        "url"=>"{:m}-{:a}-{b}",
                        "param"=>array(),
                           );
                           
$config['demo/index'][]=array(
                        "url"=>"hello-{b}-{c}",
                        "param"=>array(
                           "c"=>'\d*'   
                            ),
                           );

$config['demo/hello'][]="demohello-{a}-{b}";
$config['demo/hello'][]="demohello-{b}";   
                                                   
$config['demo/hello'][]=array(
                        "url"=>"demohello-{a}",
                        "param"=>array(
                             "a"=>"\d+",
                             ),
                           );

                           


return $config;