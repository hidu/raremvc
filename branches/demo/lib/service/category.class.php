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
     $list=array();
     foreach ($all as $one){
       $list[$one['cateid']]=$one;
     }
     return $list;
   }
   
   public static function getCate($cateid){
     return self::getDao()->getOneByKey($cateid);
   }
   
   public static function getByPinyin($pinyin){
     return self::getDao()->getOneByField('pinyin', $pinyin);
   }

}