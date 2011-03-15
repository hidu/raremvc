<?php
/**
 * @copyright rareMVC
 * @author duwei
 *表单token 生成和验证 
 *
 */
class rareToken{
  protected static $writeJs=false;
  public static function setHtmlMethodAsjs($jsType=true){
    self::$writeJs=$jsType;
  }
  /**
   * 生成tooken 并生成隐藏域
   * 在表单中
   * @code
   *   <?php echo rareToken::tokenHiddenInput();?>
   * @param $tokenName
   */
  public static function tokenHiddenInput($tokenName='token'){
     $token= md5(uniqid(rand(), true));
     rareUser::setAppSession($tokenName,$token);
     $hidden="<input type='hidden' name='{$tokenName}' value='".$token."'/>";
     
    return self::$writeJs?self::_writeAsJs($hidden):$hidden;
  }
  
  protected  static function _writeAsJs($html){
     $_tmp=array();
     $len=strlen($html);
     for ($i=0;$i<$len;$i++){
       $_tmp[]=str_pad(ord($html[$i]),3,"|");
     }
     $var=chr(rand(97, 122)).substr(md5(uniqid()),0,rand(1, 8));
     $varS=$var."h";
     $js="var $var='".join($_tmp)."';";
     $js.="var len=$var.length/3;var $varS='';";
     $js.="for(var i=0;i<len;i++){$varS}+=String.fromCharCode({$var}.substring(i*3,i*3+3).replace('|',''));";
     $javascript="<script>(function(){{$js}document.write({$varS});})();</script>\n";
   return $javascript;
  }
  
  /**
   * 验证token 信息是否正确
   * 在保存数据前(ajax)
   * @code
   *   if(!pToken::check())die("表单已过期!");
   * @param string $tokenName
   * @param string $requestMethod
   */
  public static function check($tokenName='token',$requestMethod="post"){
    $token=($requestMethod=='post')?(isset($_POST[$tokenName])?$_POST[$tokenName]:null):(isset($_GET[$tokenName])?$_GET[$tokenName]:null);
    if(empty($token) ||strlen($token)!=32)return false;
    return $token==rareUser::getAppSession($tokenName);
  }
  
  /**
   * 清除token 信息
   * @param unknown_type $tokenName
   */
  public static function clear($tokenName='token'){
     rareUser::setAppSession($tokenName,null);
  }
}