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
       rareView::setTitle('home');
      
       if($cateid){
         $where.=" cateid=:cateid";
         $param['cateid']=$cateid;
       }
       $where.=(empty($where)?"1":"")." order by mtime desc";
       $listPagt=$articleDao->getListPage($where,$param,3);
       $this->assign('listPage',$listPagt);
    }
    public function executePost(){
    
    }
}

