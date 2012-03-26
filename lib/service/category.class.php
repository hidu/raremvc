<?php
class service_category{
  public static function getDao(){
    return service_daoFactory::getCateDao();
  }
  
   public static function getAllPair(){
     return self::getDao()->queryAllPairs('cateid', 'catename');
   }
   
   public static function getAll(){
     $all= self::getDao()->queryAll();
     return qArray::toHashmap($all, 'cateid');
   }
   
   public static function getCate($cateid){
     return self::getDao()->getByKey($cateid);
   }
   
   public static function getByPinyin($pinyin){
     return self::getDao()->getOneByField('pinyin', $pinyin);
   }

}