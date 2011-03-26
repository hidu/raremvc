<?php
/**
 * 验证类
 * @author duwei
 *
 */
class rValidate_rule{
  /**
   * 必填项
   * @param $name
   * @param $value
   * @return boolean
   */
  public static function required($value){
      if(is_array($value) && $value){
          foreach ($value as $_tmp){
             if(strlen(trim($tmp))) return true;
          }
      }
       return strlen(trim($value))>0;
  }
  
  public static function minlength($value,$length){
    return mb_strlen(trim($value),'utf-8')>=$length;
  }
  
  public static function maxlength($value,$length){
    return mb_strlen($value,'utf-8')<=$length;
  }
  public static function equalTo($value,$to,$allvalue){
    return strcmp($value,$allvalue[$to])==0;
  }
  public static function email($value){
      return preg_match("/^([a-zA-Z0-9_-])+([.a-zA-Z0-9_-])*([a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/",$value);
  }
  
  public static function url($value){
     return preg_match("/^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i",$value);
  }
  /**
   * iso 格式的日期 eg 2008-08-08  
   * @param $value
   * @return boolean
   */
  public static function dateISO($value){
      $tmp=explode("-",$value);
     return checkdate($tmp[1], $tmp[2], $tmp[0]);
  }
  /**
   * 是数字
   * @param $value
   * @return boolean
   */
  public static function number($value){
     return is_numeric($value);
  }
  /**
   * 是否比指定值小
   * @param $value
   * @param $min
   * @return boolean
   */
  public static function min($value,$min){
     return $value>=$min;
  }
  
  /**
   * 是否比指定值大
   * @param $value
   * @param $max
   * @return boolean
   */
  public static function max($value,$max){
     return $value<=$max;
  }
  
  /**
   * 枚举类型
   * @param $value
   * @param $enum
   * @return boolean
   */
  public static function enum($value,$enum){
     return in_array($value,$enum);
  }
  /**
   * 长度在指定之间
   * @param $value
   * @param $rangLen
   * @return boolean
   */
  public static function rangelength($value,$rangLen){
     return self::minlength($value,$rangLen[0]) && self::maxlength($value,$rangLen[1]);
  }
  /**
   * 数值大小在指定值之间
   * @param $value
   * @param $rang
   * @return boolean
   */
  public static function range($value,$rang){
     return self::min($value,$rang[0]) && self::max($value,$rang[1]);
  }
  /**
   * 电话
   * @param $value
   * @return boolean
   */
  public static function phone($value){
     $rt=preg_match("/^0[0-9]{2,3}-[0-9]{7,8}(-[0-9]{1,6}){0,1}$/",$value);//座机号
     if($rt)return true;
     $rt=preg_match("/^1[0-9]{10}$/",$value);//手机
     if($rt)return true;
     $rt=preg_match("/^[48]00-[0-9]{3}-[0-9]{4}$/",$value);//400 800
     if($rt)return true;
  }
  /**
   * 长度
   * @param $value
   * @param $length
   * @return boolean
   */
  public static function length($value,$length){
    return mb_strlen($value,'utf-8')==$length;
  }
  
  /**
   * 整形
   * @param $value
   * @return boolean
   */
  public static function int($value){
    return preg_match("/^\-?([0-9])+$/",$value);
  }
  
  /**
   * 邮编
   * @param $value
   * @return boolean
   */
  public static function postCode($value){
    return strlen($value)==6 && self::int($value);
  }
  
  /**
   * 验证单词数目
   * @param $value
   * @param $maxWord
   * @return boolean
   */
  public static function maxWords($value,$maxWord){
    return count(explode(',',$value))<=$maxWord;
  }
  
  /**
   * 是否是域名 如 rare.hongtao3.com
   * @param $value
   * @return boolean
   */
  public static function domain($value){
      return self::url("http://".$value);
  }
  
  /**
   * 验证是否是时间 eg 23:59:59
   * @param $value
   * @return boolean
   */
  public static function time($value){
      $tmp=explode(":",$value);
      return count($tmp)==3 && self::range($tmp[0],array(0,23)) && self::range($tmp[1],array(0,59)) && self::range($tmp[2],array(0,59));
  }
  
  /**
   * 验证指定值不能比其他一个值大
   * @param $value
   * @param $to
   * @param $all
   * @return boolean
   */
  public static function smallThan($value,$to,$all){
  	if(isset($all[$to])){
      return $value<=$all[$to];
  	}
  	return true;
  }
  
  /**
   * 值不能等于
   * @param $value
   * @param $notValues  多个值用  | 分割
   * @return boolean
   */
  public static function notIn($value,$notValues){
      $values=explode("|", $notValues);
      return !in_array($value,$values);
  }

  
  /**
   * 正则
   * @param $value
   * @param $regex
   * @return boolean
   */
  public static function regex($value,$regex){
    return !(!(preg_match($regex, $value)));
  }
}
