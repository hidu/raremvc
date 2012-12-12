<?php
/**
 *@copyright rareMVC
 *@author duwei
 *@package addon\cache
 *@example
 * 页面缓存
 * 默认采用文件缓存,也可以修改为其他缓存方式  如 memcache
 * <?php
 *  if(!rarePageCahce::has("key")){
 *     echo "这里是要缓存的内容";
 *     rarePageCahce::save(1000);//保存1000秒
 *  } 
 *
 */
class rPageCahce{
   private static $curentKeys=array();
   private static $cacheHandle;      //缓存句柄
   public  static $defaultLifeTime=0;//默认有效期
    
   public static function has($key){
        $cache=self::getCacheHandle();
        $key='pageCache/'.$key;
        if($cache->has($key)){
          echo $cache->get($key);
          return true;
        }else{
          array_push(self::$curentKeys,$key);      
          ob_start();
          ob_implicit_flush(0);
          return false;
        }
   }
   
   public static function save($lifeTime = 0){
        $data = ob_get_clean();
        $key=array_pop(self::$curentKeys);
        $cache=self::getCacheHandle();
        $cache->set($key,$data,$lifeTime?$lifeTime:self::$defaultLifeTime);
        echo $data;
   }
   
   /**
    * @return rareCache
    */
   private static function getCacheHandle(){
        if(is_null(self::$cacheHandle))self::$cacheHandle=new rCache_file();
        return self::$cacheHandle;
   }
   
   /**
    * 设置cache 句柄
    * @param rareCache $cacheObj
    */
   public static function setCacheHandle($cacheObj){
       self::$cacheHandle=$cacheObj;
   }
}