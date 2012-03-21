<?php
class indexAction extends rareAction{
    public function preExecute(){
    
    }
    public function execute(){
    
    }
    public function executeGet(){
       $articleDao=service_daoFactory::getArticleDao();
       $listPagt=$articleDao->getListPage();
       $this->assign('listPage',$listPagt);
    }
    public function executePost(){
    
    }
}

