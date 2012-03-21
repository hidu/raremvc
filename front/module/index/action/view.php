<?php
class viewAction extends rareAction{
  public function executeGet(){
    $articleid=$this->_getParam('articleid');
    $article=service_daoFactory::getArticleDao()->getByKey($articleid);
    rare_go404If(!$article);
    $this->assign('article',$article);
  }
}