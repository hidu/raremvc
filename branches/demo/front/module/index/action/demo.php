<?php
//注意 该action的类名，使用的是包含 moduleName的全称
class indexDemoAction extends rareAction{
    //本action只响应 get请求，其他方式的请求 如POST 会抛出404
    public function executeGet(){
    
    }
}

