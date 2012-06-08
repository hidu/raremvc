<?php
/**
 * 地域相关功能
 * @author duwei
 *
 */
class rRegion{
    
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
    
    public static function getProvinceName($pid){
        $all=self::getAllProvince();
        return isset($all[$pid])?$all[$pid]:null;
    }
    
    
    public static function getCityNameByCityId($cityId){
       $cities=self::getCitiesByProvinceId(substr($cityId."", 0,2)."0000");
       return isset($cities[$cityId])?$cities[$cityId]:null;  
    }
}