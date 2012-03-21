<?php
class service_article{
   
   public static function save($article){
      if(empty($article) || !is_array($article))throw new Exception("article is not valid array!");
      try{
        $new=service_daoFactory::getArticleDao()->save($article);
      }catch(Exception $e){
         trigger_error($e->getMessage());
         throw new Exception("something wrong\n".$e->getMessage());
         return false; 
       }
      return $new;
   }

}