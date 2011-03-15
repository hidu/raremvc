<?php
define('rare_Session_appName', defined("AppName")?AppName:"rare");
/**
 *@copyright rareMVC 
 *@author duwei
 *统一的用户SESSION基础类
 */
class rareUser{
   const USER_ID="user_id";//用户ID
   const USER_LOGONNAME='user_logonName';//用户登录帐号
   const USER_REALNAME="user_realName";//用户真实名称、昵称
   const USER_INFO="user_info";//用户详细信息
   
   /**
    *是否登录 
    */
   public static function isLogin(){
       return self::getUserID()>0;
   }
   
   /**
    *获取用户ID 
    */
   public static function getUserID(){
       return self::get(self::USER_ID);
   }
   
   /**
    * 获取登录帐号
    * @return string
    */
   public static function getLogonName(){
      return self::get(self::USER_LOGONNAME,'');
   }
   
   public static function set($key,$value){
       $_SESSION[$key]=$value;
   }
   
   public static function get($key,$default=""){
     return isset($_SESSION[$key])?$_SESSION[$key]:$default;
   }
   
   public static function setAppSession($key,$value){
       self::set(rare_Session_appName."/".$key, $value);
   }

   public static function getAppSession($key,$default=""){
       return self::get($key,$default);
   }
   
   
}