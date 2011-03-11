<?php
class restAction extends rareAction{
   public function execute(){
     echo url("asd/inasd");
     echo url("asd/index");
     echo url("asd/index/");
     echo url("asd/");
     echo url("asd/cindex");
     echo url("/asd/cindex");
     echo url("/asd/cindex/");
     
     die;
   }
   
   public function executePost(){
       jsonReturn(1,"you say:".$this->getRequestParam("name")."\n from executePost");
   }
}