<?php
/**
 * 地域相关功能
 * @author duwei
 *
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
    
    public static function getAllProvince(){
        return self::load("prov.php");
    }
    
    public static function getCitiesByProvinceId($pid){
        $allCities=self::load("city.php");
        return isset($allCities[$pid])?$allCities[$pid]:array();
    }
    
    public static function getCountiesByCityId($cityId){
        $allCounties=self::load("county.php");
        return isset($allCounties[$cityId])?$allCounties[$cityId]:array();
    }
    
    public static function isZhiXiaShi($provinceId){
        return in_array($provinceId, self::$zhixiashi);
    }
    
    public static function getProvinceName($pid){
        $all=self::getAllProvince();
        return isset($all[$pid])?$all[$pid]:null;
    }
    
    
    public static function getCityNameByCityId($cityId,$full=false){
        print_r($cityId);
       $pid=substr($cityId."", 0,2)."0000";
       $cities=self::getCitiesByProvinceId($pid);
       return isset($cities[$cityId])?$cities[$cityId].($full?"-".self::getProvinceName($pid):""):null;  
    }
    
    public static function getCountyNameByCountyId($countyId,$full=false){
        $pid=substr($countyId."", 0,2)."0000";
        if(self::isZhiXiaShi($pid)){
           $cityId=substr($countyId."", 0,2)."1000";
        }else{
           $cityId=substr($countyId."", 0,4)."00";
        }
        $counties=self::getCountiesByCityId($cityId);
        print_r($countyId);
        return isset($counties[$countyId])?($full?self::getCityNameByCityId($cityId,$full)."-":"").$counties[$countyId]:null;
    }
}
echo rRegion::getCountyNameByCountyId(110116,true);