<?php
/**
 *统一的用户SESSION基础类
 *@copyright rareMVC 
 *@author duwei
 *@package addon\session
 */
class rUser{
   /**
    * 用户ID
    * @var int
    */
   const USER_ID="USER_ID";
   
   /**
    * 用户登录帐号
    * @var string
    */
   const USER_LOGONNAME='USER_logonName';
   
   /**
    * 用户真实名称、昵称
    * @var string
    */
   const USER_REALNAME="USER_realName";
   
   /**
    * 用户详细信息,通常是对于用户表的一条信息
    * @var array
    */
   const USER_INFO="USER_INFO";
   
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
   
   /**
    * 获取真实姓名
    * @param boolean $fill 当为空是返回登录名
    * @return string
    */
   public static function getRealName($fill=false){
      return self::get(self::USER_REALNAME,$fill?self::getLogonName():"");
   }
   
   
   
   public static function set($key,$value){
       $_SESSION[$key]=$value;
   }
   
   public static function get($key,$default=""){
     return isset($_SESSION[$key])?$_SESSION[$key]:$default;
   }
   
   public static function setAppSession($key,$value){
       self::set(RARE_APP_NAME."/".$key, $value);
   }

   public static function getAppSession($key,$default=""){
       return self::get(RARE_APP_NAME."/".$key,$default);
   }
   
   /**
    * 清除指定sessionID的session数据
    * @param string $sessionID
    */
   public static function destoryOtherSession($sessionID,$sessionName=null){
         if(is_null($sessionName))$sessionName=session_name();
         if(function_exists("proc_open")){
             proc_close(proc_open("php ".dirname(__FILE__)."/_destroy.php ".$sessionName." ".$sessionID." &",array(), $foo));
          }
   }
}