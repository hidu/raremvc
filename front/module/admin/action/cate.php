<?php
class cateAction extends rareAction{
  public function preExecute(){
    $nav_left="&gt;&gt;分类";
    slot_set('nav_left', $nav_left);
  }
  
   public function executeGet(){
     $listPage=service_daoFactory::getCateDao()->getListPage();
     $this->assign('listPage',$listPage);
   }
   public function executeEdit(){
      $cate=$this->_postParam('c');
      if(empty($cate))redirect('admin/cate');

     $validate=new rValidate('cate');
     if(!$validate->validate($cate)){
       rHtml::js_alertGo(str_replace("\n","",$validate->getErrorsAsString()),-1);
     }
      try{
      $new=service_daoFactory::getCateDao()->save($cate);
      }catch (Exception $e){
       rHtml::js_alertGo($e->getMessage(), -1);
       }
      redirect('admin/cate?method=edit&cateid='.$new['cateid']);
   }
   
   public function executeDelete(){
     $cateid=$this->_postParam('cateid');
     if(empty($cateid)){
       rHtml::js_alertGo('empty cateid', -1);
      }
     $rt=service_daoFactory::getCateDao()->deleteByKey($cateid);
     if(false ===$rt){
          rHtml::js_alertGo('try again', -1);
     }
       redirect('admin/cate');
   }
}