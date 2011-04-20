<?php
/**
 * @copyright rareMVC
 * @author duwei
 * html表单输出工具类
 */
class rHtml{
   private static $autoID=true;
   /**
    * 是否允许自动添加id字段
    * @param boolean $enable
    */
   public static function enableAutoID($enable){
       self::$autoID=$enable;
   }
    
   public static function select($name,$value,$options,$params=''){
       $html="<select name=\"{$name}\" ".self::_paramMerge($params,array('id'=>self::getIDByName($name)),true).">";
       if(!is_array($value))$value=explode(",", $value);
       $value=array_flip($value);
       foreach ($options as $_k=>$_v){
           $html.="<option value=\"".self::h($_k)."\" ".(array_key_exists($_k, $value)?'selected="selected"':"").">".self::h($_v)."</option>";
        }
        return $html."</select>";
   }
   
  
   
   public static function textArea($name,$value='',$params=''){
     return '<textarea name="'.$name.'" '.self::_paramMerge(array('id'=>self::getIDByName($name)),$params,true).">".self::h($value)."</textarea>";
   }
   
   public static function input_radio($name,$value,$options,$params=""){
       $html="";
       foreach ($options as $_k=>$_v){
           $_param=array();
            $_param['id']=self::getIDByName($name)."_{$_k}";
           if($_k==$value)$_param['checked']="checked";
           $html.="<label>".self::inputTag('radio',$name,$_k,$params,$_param).self::h($_v)."</label>";
        }
        return $html;
   }
   
   public static function input_checkbox($name,$value,$options,$params=''){
      if(!is_array($options))$options=array($options=>'');
      if(count($options)==1){
          list($_k,$_v)=each($options);
          $_param=array();
          if($_k==$value)$_param['checked']="checked";
          return "<label>".self::inputTag('checkbox',$name,$_k,$params,$_param)."{$_v}</label>";
      }else{
          if(!is_array($value))$value=explode(",",$value);
          $html="";
          foreach ($options as $_k=>$_v){
              $_param=array();
              if(in_array($_k, $value))$_param['checked']="checked";
              $_param['id']=self::getIDByName($name)."_".$_k;
              $html.="<label>".self::inputTag('checkbox',$name,$_k,$params,$_param)."{$_v}</label>";
           }
          return $html;
      }
   }
   
   public static function checkbox($name,$value,$itemValue,$params=''){
       $_param=array();
       if($itemValue ===true || $value==$itemValue)$_param['checked']='true';
       if(str_endWith($name,"[]")){
           $_param['id']=self::getIDByName($name)."_".$value;
        }
       return self::inputTag('checkbox',$name,$value,$params,$_param);
   }
   
   public static function input($name,$value="",$params=""){
     return self::inputTag('text',$name,$value,$params);
   }
   
   public static function input_hidden($name,$value){
       return self::inputTag('hidden',$name,$value);
   }
   
   public static function input_file($name,$params=""){
      return self::inputTag('file',$name,"",$params);
   }
   
   public static function input_password($name,$value='',$params=""){
      return self::inputTag('password',$name,$value,$params);
   }
   
   public static function input_image($src,$params=""){
      return self::inputTag("image",'',$label,$params,array('src'=>$src)); 
   }
   
   public static function input_button($label,$params=""){
      return self::inputTag("button",'',$label,$params); 
   }
   
   public static function input_submit($label='',$params=""){
      return self::inputTag("submit",'',$label,$params); 
   }
   
   public static function submit($label="",$params=""){
      return self::input_submit($label,$params);
   }
   
   
   public static function input_reset($label,$params=''){
      return self::inputTag("reset",'',$label,$params); 
   }
   
   /**
    * html5
    * @param string $name
    * @param array|string $params
    */
   public static function input_email($name,$params=''){
      return self::inputTag('email',$name,$value,$params);
   }
   
   /**
    * html5
    * @param string $name
    * @param mix $params
    */
   public static function input_search($name,$params=''){
      return self::inputTag('search',$name,$value,$params);
   }
   
   /**
    * html5
    * @param string $name
    * @param mix $params
    */
   public static function input_url($name,$params=''){
      return self::inputTag('url',$name,$value,$params);
   }
   
   
   public static function h($value){
       return htmlspecialchars($value);
   }
   
   public static function getIDByName($name){
        return trim(str_replace(array("][","[","]"),array("_","_",""),$name),"_");
    }
    
    public static function a($url,$text,$params=''){
         if(!_rare_isUrl($url) && !str_startWith($url, '#') && !str_startWith($url, "javascript:")){
           $url=url($url);
         }
        return '<a href="'.$url.'" '.self::_paramMerge($params,true).">".self::h($text)."</a>";
    }
    
    public static function js_alertGo($message,$url){
        $go=is_int($url)?"history.go($url)":"location.href='{$url}'";
        echo'<script>'.(strlen($message)?'alert("'.self::h($message).'");':'').$go.';</script>';
        die;
    }
    
   
   public static function inputTag($type='text',$name='',$value='',$param="",$paramMore=''){
       $_param=array();
       if($name){
          $_param['name']=$name;
          if(self::$autoID){
            $_param['id']=self::getIDByName($name);
            }
       }
       $param=self::_paramMerge($param, $_param,$paramMore,true);
     return "<input type=\"{$type}\" value=\"".self::h($value)."\" {$param} />";
   }
   
    
    private static function  _paramMerge(){
         $numargs = func_num_args();
         $param=array();
         for($i=0;$i<$numargs;$i++){
            $_param=func_get_arg($i);
            if($numargs-1==$i && $_param==true)continue;//最后一个参数为true
           if(is_string($_param))$_param=sfToolkit::stringToArray($_param);
           if(!is_array($_param))$_param=array();
           $param=array_merge($param,$_param);
          }
         if($_param===true){
               $str="";
              foreach ($param as $_k=>$_v){
                    $str.=$_k.'="'.self::h($_v).'" ';
                 }
               return $str;
          }
       return $param;
    }
    
    /**
     *html5 
     * @param string $id
     * @param string|array $values
     */
    public static function datalist($id,$values){
        if(is_string($values))$values=explode(",", $values);
        $html='<datalist id="'.$id.'">';
        foreach ($values as $val){
            $html.='<option value="'.self::h($val).">";
         }
         return $html."</datalist>";
    }
   
}