<?php
class rI18n{
  /**
   * 客户端接受的语言
   * @var array
   */ 
  private static $accepts=null;
  
  /**
   * 默认语言
   * @var string
   */
  private static $current=null;
  
  /**
   * 判断指定的语言客户端是否支持
   * @param string $lang
   */
  public static function isSupport($lang){
     $accepts=self::getAccept();
     return in_array($lang, $accepts);
  }
   
   
   public static function getAccept(){
     if(self::$accepts==null){
        if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
          self::$accepts=array();
         }else{
           $languages=explode(",",str_replace(";", ',', $_SERVER['HTTP_ACCEPT_LANGUAGE']));
            foreach ($languages as $language){
               if(strstr($language, "-")){
                    self::$accepts[]=$language;
                 }
              }
         }
     }
     return self::$accepts;
   }
   /**
    * 设置当前语言
    * @param string $lang
    */
   public static function setLang($lang){
     self::$current=$lang;
   }
   
   /**
    *获取当前语言 
    */
   public static function getLang(){
     if(self::$current==null){
        $accepts=self::getAccept();
        self::$current=self::$accepts?self::$accepts[0]:'zh-cn';
     }
     return self::$current;
   }
}