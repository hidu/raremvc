<?php
class aAction extends rareAction{
  public function execute(){
     $this->forward("demo/hello?a=1&b=2");
  }
}