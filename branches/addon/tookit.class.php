<?php
/**
 *@copyright rareMVC 
 *@author duwei
 *通用工具类
 */
class rTookit{
   
   /**
    * 支持html的字符串截取
    * @param string $html
    * @param int $length
    */
   public static function cutHtml($html,$length=300){
      $html=strip_tags($html);
      return self::cutStr($html,$length);
   }
   
   public static function cutStr($str,$length=50){
       preg_match_all("/./us", $str, $ar);
       $newstring = join("", array_slice($ar[0], 0, $length));
       return $newstring;
   }
  
  public static function addslashesDeep($value)
  {
    return is_array($value) ? array_map(array('rTookit', 'addslashesDeep'), $value) : addslashes($value);
  }
  
  public static function stripslashesDeep($value)
  {
    return sfToolkit::stripslashesDeep($value);
  }
  
  /**
   * 设置 magic_quotes_gpc
   * @param boolean $isQuote
   */
  public static function set_magic_quotes_gpc($isQuote=0){
    $isQuote=strtolower($isQuote);
    if(!$isQuote || $isQuote=='off')$isQuote=0;
    if($isQuote)$isQuote=1;
    if(get_magic_quotes_gpc()==$isQuote)return;
    
    if(!$isQuote){
        $tmp=sfToolkit::stripslashesDeep($_POST);
        foreach ($tmp as $k=>$v){
          $_POST[$k]=$v;
         }
        $tmp=sfToolkit::stripslashesDeep($_GET);
        foreach ($tmp as $k=>$v){
          $_GET[$k]=$v;
         }
        $tmp=sfToolkit::stripslashesDeep($_COOKIE);
        foreach ($tmp as $k=>$v){
          $_COOKIE[$k]=$v;
         }
        $tmp=sfToolkit::stripslashesDeep($_REQUEST);
        foreach ($tmp as $k=>$v){
          $_REQUEST[$k]=$v;
         }
     }else{
        $tmp=self::addslashesDeep($_POST);
        foreach ($tmp as $k=>$v){
          $_POST[$k]=$v;
         }
        $tmp=self::addslashesDeep($_GET);
        foreach ($tmp as $k=>$v){
          $_GET[$k]=$v;
         }
        $tmp=self::addslashesDeep($_COOKIE);
        foreach ($tmp as $k=>$v){
          $_COOKIE[$k]=$v;
         }
        $tmp=self::addslashesDeep($_REQUEST);
        foreach ($tmp as $k=>$v){
          $_REQUEST[$k]=$v;
         }
     }
  }
}