<?php
/**
 *@copyright rareMVC 
 *@author duwei
 *通用工具类
 */
class rareTookit{
    
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
             $tmp[$_subArray[$key]] = $_subArray[$key];
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
}