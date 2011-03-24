<?php
/**
 *rare 路由解析 
 * http://raremvc.googlecode.com
 * http://rare.hongtao3.com
 * 20110324 更新
 * @package rare
 * @author duwei
 * @version  $Id: rareRouter.class.php duwei $
 */
class rareRouter{
   private static $config; 
   /**
    * 初始化路由规则 
    */
   public static function init(){
       $config=rareConfig::getAll("router");
       if(!$config){
         self::$config=false;
         return;
       }
       foreach ($config as $actionFullName=>$items){
           $tmp=explode("/", $actionFullName);
           if(is_string($items))$items=array(array('url'=>$items));
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
  
   /**
    * 解析路由
    * @param string $uri
    */
   public static function parse($uri){
       if(!self::$config)return $uri;
       $tmp=parse_url($uri);
       $path=isset($tmp['path'])?trim($tmp['path'],"/"):"index/index";
       $path=preg_replace("/\.\w*$/", "", $path);
       foreach (self::$config as $actionName=>$action){
           foreach ($action as $actionUrl){
               if(preg_match_all("#^".$actionUrl['url_reg']."$#",$path,$matches,PREG_SET_ORDER)){
                     
                     array_shift($matches[0]);
                     $tmp1=array();
                     foreach ($actionUrl['_params'] as $k=>$v){
                          $tmp1[strtr($k,array("{"=>'',"}"=>''))]=urldecode(array_shift($matches[0]));
                       }
                     //---------------------
                     /*若路由配置中有配置fn(使用自定义还是进行确实地址),则运行相应函数
                     *example:
                     *<?class myClass{
                     *  public  function edit($path,$actionName,$param=array()){
                     *      return in_array($param['city'],array('qingdao','beijing','wuhan'));
                     *  }
                     *}
                     **/  
                     if(isset($actionUrl['fn'])){
                        $fn=explode("::", $actionUrl['fn']);
                        if(class_exists($fn[0],true)){
                            if(!call_user_func_array($fn, array($path,$actionName,$tmp1)))continue;
                        }
                     }
                     //=====================
                     foreach ($tmp1 as $_k=>$_v){
                         $_GET[$_k]=$_REQUEST[$_k]=$_v;
                     }
                    return $actionName."?".http_build_query($tmp1);
                 }
             }
        }
        return $uri;
   }
   
   /**
    * 根据路由规则生成新地址
    * @param string $actionFullName
    * @param array $query
    */
   public static function generate($actionFullName,$query){
       if(!self::$config || !isset(self::$config[$actionFullName]))return;
       $config=self::$config[$actionFullName];
       foreach ($config as $action){
           if(count($action['param'])>count($query))continue;
           $isMatch=true;$_params=array();
           foreach ($action['param'] as $k=>$reg){
               if(!isset($query[$k]) || !preg_match("#^".$reg."$#", $query[$k])){
                    $isMatch=false;
                     break;
                  }
               $_params["{".$k."}"]=urlencode($query[$k]);
               unset($query[$k]);
             }
            if(!$isMatch)continue;
            $url=strtr($action['url_param'], $_params);
          return array($url,$query,isset($action['suffix'])?$action['suffix']:null);
       }
   }
}
