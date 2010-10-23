<?php
class aAction extends rareAction{
  public function execute(){
     $this->forward("demo/hello");
  }
}