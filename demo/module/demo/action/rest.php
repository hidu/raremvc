<?php
class restAction extends rareAction{
   public function execute(){
   
   }
   
   public function executePost(){
       jsonReturn(1,"you say:".$this->getRequestParam("name")."\n from executePost");
   }
}