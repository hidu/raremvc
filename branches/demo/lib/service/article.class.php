<?php
class service_article{
  
  public static function getDao(){
     return service_daoFactory::getArticleDao();
  } 
  
   public static function save($article){
     return self::getDao()->save($article);
   }
   
   public static function getLast($num){
       $where="order by mtime desc limit {$num}";
       return self::getDao()->queryAll($where);
   }
   
   public static function getArticle($articleid){
     return self::getDao()->getOneByKey($articleid);
   }

   
}