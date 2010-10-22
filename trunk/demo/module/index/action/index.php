<?php
class indexAction extends rareAction{
    public function execute(){
        $this->vars['a']=date("Y-m-d");
//        $this->display();
         return true;
    }
}