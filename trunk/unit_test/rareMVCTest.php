<?php
require_once 'PHPUnit/Autoload.php';
include_once '../rareMVC.class.php';
class rareMVCTest extends PHPUnit_Framework_TestCase{
    protected  $context=null;
    
    public function setUp(){
           $this->context=rareContext::createApp(dirname(__FILE__));
    }
    
    
    public function testCheckCycle(){
        $uri="?a=1&b=2";
        $info=array('m'=>'index','a'=>'index','q'=>'a=1&b=2',"u"=>'index/index');
        $arr=$this->context->parseActionUri($uri);
        $this->assertEquals($arr, $info);
    }
    
    public function testParseActionUri_case1(){
        $uri="index?a=1&b=2";
        $info=array('m'=>'index','a'=>'index','q'=>'a=1&b=2',"u"=>'index/index');
        $arr=$this->context->parseActionUri($uri);
        $this->assertEquals($arr, $info);
    }
}