<?php
/**
 * 地域相关功能,参照 国家 行政区划代码
 * @author duwei <duv123@gmail.com>
 */
class rRegion{
    
    private  static $zhixiashi=array("110000","120000","310000","500000");
    
    private static function load($name){
        static $file_data=array();
        if(!isset($file_data[$name])){
            $file_data[$name]=require dirname(__FILE__)."/data/".$name;
          } 
         return   $file_data[$name];
    }
    
    /**
     * 获取所有的省份
     * @return array
     */
    public static function getAllProvince(){
        return self::load("prov.php");
    }
    
    /**
     * 获取省下的市
     * @param int $pid
     * @return array
     */
    public static function getCitiesByProvinceId($pid){
        $allCities=self::load("city.php");
        return isset($allCities[$pid])?$allCities[$pid]:array();
    }
    
    /**
     * 获取市级下的所有县级
     * @param int $cityId
     * @return array
     */
    public static function getCountiesByCityId($cityId){
        $allCounties=self::load("county.php");
        return isset($allCounties[$cityId])?$allCounties[$cityId]:array();
    }
    
    /**
     * 判断是否直辖市
     * @param int $provinceId
     * @return boolean
     */
    public static function isZhiXiaShi($provinceId){
        return in_array($provinceId, self::$zhixiashi);
    }
    
    /**
     * 获取省级名称
     * @param string $pid
     */
    public static function getProvinceName($pid){
        $all=self::getAllProvince();
        return isset($all[$pid])?$all[$pid]:null;
    }
    
    /**
     * 获取市级名称
     * @param int $cityId
     * @param boolean $full
     * @return NULL|String
     */
    public static function getCityNameByCityId($cityId,$full=false){
       $pid=self::getProvinceIdByCityId($cityId);
       $cities=self::getCitiesByProvinceId($pid);
       if(!isset($cities[$cityId]))return null;
       if(!$full)return $cities[$cityId];
       $names=array();
       $names[]=self::getProvinceName($pid);
       $names[]=$cities[$cityId];
       return join("-", array_unique($names));
    }
    
    /**
     * 获取县级的名称
     * @param int $countyId
     * @param boolean $full  是否完整地址
     */
    public static function getCountyNameByCountyId($countyId,$full=false){
        $cityId=self::getCityIdByCountyId($countyId);
        $counties=self::getCountiesByCityId($cityId);
        if(!isset($counties[$countyId]))return null;
        if(!$full)return $counties[$countyId];
        $names=explode("-", self::getCityNameByCityId($cityId,$full));
        $names[]=$counties[$countyId];
        return join("-", array_unique($names));
    }
    
    public static function getCityIdByCountyId($countyId){
        return substr($countyId."", 0,4)."00";;
    }
    
    public static function getProvinceIdByCityId($cityId){
        return  substr($cityId."", 0,2)."0000";
    }
    
    public static function getProvinceIdByCountyId($countyId){
        return  substr($countyId."", 0,2)."0000";
    }
}