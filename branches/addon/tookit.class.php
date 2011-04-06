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
}