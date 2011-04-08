<?php
/**
 *@copyright rareMVC 
 *@author duwei
 *通用工具类
 */
class rTookit{
    
    /**
     *Converts string to array
     * see symfony sfTookit.class.php 
     * @param $string
     */
   public static function string2Array($string){
       preg_match_all('/
          \s*(\w+)              # key                               \\1
          \s*=\s*               # =
          (\'|")?               # values may be included in \' or " \\2
          (.*?)                 # value                             \\3
          (?(2) \\2)            # matching \' or " if needed        \\4
          \s*(?:
            (?=\w+\s*=) | \s*$  # followed by another key= or the end of the string
          )
        /x', $string, $matches, PREG_SET_ORDER);
       $attributes = array();
        foreach ($matches as $val)
        {
          $attributes[$val[1]] = $val[3];
        }
        return $attributes;
   }
   
   public static function arrayGetCols($array,$key){
       $tmp=array();
       foreach ($array as $_subArray){
           if (isset($_subArray[$key]))$tmp[]=$_subArray[$key];
        }
       return array_unique($tmp);
   }
   
   public static function arrayToHashMap($array,$key,$value=null){
       $tmp = array();
       if ($value){
          foreach ($array as $_subArray){
             $tmp[$_subArray[$key]] = $_subArray[$value];
           }
        }else{
            foreach ($array as $_subArray){
             $tmp[$_subArray[$key]] = $_subArray;
            }
        }
        return $tmp;
   }
   
   public static function arrayGroupBy($array,$key){
       $tmp = array();
       foreach ($array as $_subArray){
            $tmp[$_subArray[$key]][] = $_subArray;
        }
        return $$tmp;
   }
   
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
   
  public static function stripslashesDeep($value)
  {
    return is_array($value) ? array_map(array('rTookit', 'stripslashesDeep'), $value) : stripslashes($value);
  }
  
  public static function addslashesDeep($value)
  {
    return is_array($value) ? array_map(array('rTookit', 'addslashesDeep'), $value) : addslashes($value);
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
        $tmp=self::stripslashesDeep($_POST);
        foreach ($tmp as $k=>$v){
          $_POST[$k]=$v;
         }
        $tmp=self::stripslashesDeep($_GET);
        foreach ($tmp as $k=>$v){
          $_GET[$k]=$v;
         }
        $tmp=self::stripslashesDeep($_COOKIE);
        foreach ($tmp as $k=>$v){
          $_COOKIE[$k]=$v;
         }
        $tmp=self::stripslashesDeep($_REQUEST);
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