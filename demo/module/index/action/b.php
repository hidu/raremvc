<?php
class bAction extends rareAction{
    public function execute(){
        $this->vars['msg']="我和他共用了同样的视图文件";
        return "index/index";//index/view/index.php 文件
    }
}