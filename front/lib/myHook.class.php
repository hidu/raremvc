<?php
class myHook{
   public static function url($actionName,$query,$suffix){
      if($actionName == "index/view"){
           if(isset($query['articleid'])){
             $article=service_article::getArticle($query['articleid']);
             if($article && !empty($article['pinyin'])){
                  $query['pinyin']=$article['pinyin'];
                }
           }
           return;
       }
       if($actionName=="index/index"){
         if(isset($query['cateid'])){
            $cate=service_category::getCate($query['cateid']);
            $query['catepinyin']=$cate['pinyin'];
            unset($query['cateid']);
         }
         return;
       }
   }
}