<?php
class indexAction extends rareAction{
    public function execute(){
        $this->vars['date']=date("Y-m-d");
        $this->vars['msg']="欢迎使用rareMVC framework";
    }
}