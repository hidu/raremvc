<?php
class  demo_indexAction extends rareAction{
  public function execute(){
//      echo url("?aaa=b");
//    $uri=rareRouter::parse("hello-test-123");
//    dump($uri);
//    rareRouter::generate("demo/index?b=test&c=456&d=789");
//echo url('demo/hello?a=2');
//echo "<br/>";
//echo url('demo/hello?b=你好');
//echo "<br/>";
//echo url('demo/hello?b=bbb');

      echo url('demo/hello?a=2&b=1');
  }
}