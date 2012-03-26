<?php
class editAction extends rareAction{
  public function executeGet(){
     $articleID=$this->_getParam('articleid');
     $article=service_article::getArticle($articleID);
     if(!$article){
       $article=array('title'=>'','body'=>'','articleid'=>'','cateid'=>'','pinyin'=>'');
     }
     $cates=service_category::getAllPair();
     $this->assign('cates',$cates);
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
     rHtml::js_alertGo("save success!", url("?articleid=".$new['articleid']));
  }

}