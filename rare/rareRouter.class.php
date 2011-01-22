<?php
/**
 *rare 路由解析 
 * @author duwei
 *
 */
class rareRouter{
   private static $config; 
   public static function init(){
       $config=rareConfig::getAll("router");
       if(!$config){
         self::$config=false;
         return;
       }
       foreach ($config as $actionFullName=>$items){
           $tmp=explode("/", $actionFullName);
           foreach ($items as $k=>$item){
               if(is_string($item)){
                    $items[$k]=$item=array("url"=>$item);
                 }
              $url=$item['url'];
              $param=isset($item['param'])?$item['param']:array();
              preg_match_all("/\{\w*\}/", $url,$matches);
              $matches=$matches[0];
              $paramsMatch=array();
              foreach ($matches as $match){
                  $p=strtr($match,array("{"=>'',"}"=>''));
                  if(!isset($param[$p])){
                      $param[$p]=".+";
                    }
                 $paramsMatch['{'.$p."}"]="(".$param[$p].")";
                }
                
              $_url=strtr($url, array("{:m}"=>$tmp[0],"{:a}"=>$tmp[1]));
              $items[$k]['param']=$param;
              $items[$k]['url_param']=$_url;
              $items[$k]['url_reg']=strtr($_url,$paramsMatch);
              $items[$k]['_params']=$paramsMatch;
           }
           $config[$actionFullName]=$items;
       }
     self::$config=$config;
   }
  
   public static function parse($uri){
       if(!self::$config)return $uri;
       $uri=preg_replace("/\.\w*$/", "", $uri);
       $tmp=parse_url($uri);
       $path=isset($tmp['path'])?trim($tmp['path'],"/"):"index/index";
       foreach (self::$config as $actionName=>$action){
           foreach ($action as $actionUrl){
               if(preg_match_all("/".$actionUrl['url_reg']."/",$path,$matches,PREG_SET_ORDER)){
                     array_shift($matches[0]);
                     $tmp1=array();
                    foreach ($actionUrl['_params'] as $k=>$v){
                          $tmp1[strtr($k,array("{"=>'',"}"=>''))]=urldecode(array_shift($matches[0]));
                       }
                    return $actionName."?".http_build_query($tmp1);
                 }
             }
        }
        return $uri;
   }
   
   /**
    * @param string $actionFullName
    * @param array $query
    */
   public static function generate($actionFullName,$query){
       if(!self::$config || !isset(self::$config[$actionFullName]))return;
       $config=self::$config[$actionFullName];
//       dump($config);dump($query);
       foreach ($config as $action){
           $isMatch=true;$_params=array();
           if(count($action['param'])>count($query))continue;
           foreach ($action['param'] as $k=>$reg){
               if(!isset($query[$k]) || !preg_match("/".$reg."/", $query[$k])){
                    $isMatch=false;
                     break;
                  }
               $_params["{".$k."}"]=urlencode($query[$k]);
               unset($query[$k]);
             }
            if(!$isMatch)continue;
            $url=strtr($action['url_param'], $_params);
          return array($url,$query);
       }
   }
}