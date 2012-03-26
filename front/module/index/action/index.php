<?php
class indexAction extends rareAction{
    public function preExecute(){
    
    }
    public function execute(){
    
    }
    public function executeGet(){
       $cateid=(int)$this->_getParam('cateid');
       $articleDao=service_daoFactory::getArticleDao();
       $where="";
       $param=array();
       if($cateid){
         $where.=" cateid=:cateid";
         $param['cateid']=$cateid;
       }
       $listPagt=$articleDao->getListPage($where,$param);
       $this->assign('listPage',$listPagt);
    }
    public function executePost(){
    
    }
}

