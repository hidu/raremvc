<?php
class myRouter{
   public static function catePinyin($path,$actionName,$param){
      $pinyin=$param['catepinyin'];
      $cate=service_category::getByPinyin($pinyin);
      if(!$cate)return false;
      $param['cateid']=$cate['cateid'];
      return true;
   }
}