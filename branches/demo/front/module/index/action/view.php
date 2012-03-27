<?php
class viewAction extends rareAction{
  public function executeGet(){
    $articleid=$this->_getParam('articleid');
    $article=service_article::getArticle($articleid);
    
    
    rare_go404If(!$article);

    $cate=service_category::getCate($article['cateid']);
    $slot="&gt;&gt;<a href='".url('index?cateid='.$cate['cateid'])."'>".$cate['catename']."</a>&gt;&gt;";
    $slot.=h($article['title']);
    slot_set('nav_left', $slot);
    
    rareView::setTitle($article['title']);
    
    $this->assign('article',$article);
  }
}