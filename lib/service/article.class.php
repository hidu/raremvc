<?php
class service_article{
  
  public static function getDao(){
     return service_daoFactory::getArticleDao();
  } 
  
   public static function save($article){
      if(empty($article) || !is_array($article))throw new Exception("article is not valid array!");
      try{
        $new=self::getDao()->save($article);
      }catch(Exception $e){
         trigger_error($e->getMessage());
         throw new Exception("something wrong\n".$e->getMessage());
         return false; 
       }
      return $new;
   }
   
   public static function getLast($num){
       $where="1  order by mtime desc limit {$num}";
       return self::getDao()->queryAll($where);
   }
   
   public static function getArticle($articleid){
     return self::getDao()->getByKey((int)$articleid);
   }

   
}