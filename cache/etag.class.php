<?php
/**
 * etag 缓存实现方案 //HTTP/1.1协议下才生效
 * 在过滤器中调用 rCache_etag::regirest();即可 
 * 关键方法已经进行了拆分可以按照实际需求进行组装，而不用调用默认的rCache_etag::regirest();
 * @author duwei
 */
class rCache_etag{
   
  
  /**
   *注册etag相关功能 
   */
   public static function regirest(){
     if($_SERVER['REQUEST_METHOD']!='GET')return;
     register_shutdown_function(array('rCache_etag','shutdown'));
     ob_start();
   }
   
   /**
    * shutdown 时检查当前的html的etag是否相等 
    */
   public static function shutdown(){
         $html=ob_get_contents();
         ob_end_clean();
         $etag=md5($html);
         self::checkEtag($etag);
         echo $html;
   }
   
   //上面两个方法是实现参考
   ////////////////////////////////////////////////////////////////////////////////////////////
   
   /**
    * 检查当前请求指定的html内容的etag是否一致
    * 需要自己判断当前的http response code 是否是200
    * @param string $html
    */
   public static function checkEtag($etag){
        if(!self::isTextResponse())return false;
         $clientID = isset($_SERVER['HTTP_IF_NONE_MATCH'])?$_SERVER['HTTP_IF_NONE_MATCH']:'';
         header('Cache-Control: public, must-revalidate, max-age=0');
//         header('Pragma: Cache');
         header('Etag: '.$etag);
         if ($clientID == $etag) {
            header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
            exit;
         }
   }
   
   /**
    *判断当前相应内容是否为文本 
    */
   public static function isTextResponse(){
     $headerText=implode(" ", headers_list());
     return preg_match("/Content-Type\:(\s+)?text\/(html|css|javascript)/i", $headerText);
   }
}