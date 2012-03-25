<?php
class service_daoFactory{
   private static $instance=array();
   
   public static function getInstance($className){
      if(!isset(self::$instance[$className])){
        self::$instance[$className]=new $className();
       }
       return self::$instance[$className];
   }
   /**
    * 
    * @return dao_article
    */
   public static function getArticleDao(){
     
      return self::getInstance('dao_article');
   }
   
   /**
    *@return dao_category 
    */
   public static function getCateDao(){
      return self::getInstance("dao_category");
   }
}