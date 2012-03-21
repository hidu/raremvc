<?php
class editAction extends rareAction{
  public function executeGet(){
     $articleID=$this->_getParam('articleID');
     $article=array('title'=>'','body'=>'');
     $this->assign('article',$article);
  }
  
  public function executePost(){
    $article=$this->_postParam('a');
    try{
    $new=service_article::save($article);
    }catch(Exception $e){
      rHtml::js_alertGo($e->getMessage(), -1);
    }
    if(!$new)rHtml::js_alertGo("save fail!try again!", -1);
    redirect(url("?articleid=".$new['articleid']));
  }

}