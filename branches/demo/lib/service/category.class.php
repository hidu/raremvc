<?php
class service_category{
   public static function getAllPair(){
     return service_daoFactory::getCateDao()->queryAllPairs('cateid', 'catename');
   }

}