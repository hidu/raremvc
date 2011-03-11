<?php
class aAction extends rareAction{
  public function execute(){
      echo "aaa";
     forward("demo/hello?a=1&b=2");
  }
}