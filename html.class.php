<?php
/**
 * @copyright rareMVC
 * @author duwei
 * html表单输出工具类
 */
class rHtml{
    
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
           $html.="<label>".self::_input('radio',$name,$_k,$params,$_param).self::h($_v)."</label>";
        }
        return $html;
   }
   
   public static function input_checkbox($name,$value,$options,$params=''){
      if(!is_array($options))$options=array($options=>'');
      if(count($options)==1){
          list($_k,$_v)=each($options);
          $_param=array();
          if($_k==$value)$_param['checked']="checked";
          return "<label>".self::_input('checkbox',$name,$_k,$params,$_param)."{$_v}</label>";
      }else{
          if(!is_array($value))$value=explode(",",$value);
          $html="";
          foreach ($options as $_k=>$_v){
              $_param=array();
              if(in_array($_k, $value))$_param['checked']="checked";
              $_param['id']=self::getIDByName($name)."_".$_k;
              $html.="<label>".self::_input('checkbox',$name,$_k,$params,$_param)."{$_v}</label>";
           }
          return $html;
      }
   }
   
   public static function input($name,$value="",$params=""){
     return self::_input('text',$name,$value,$params);
   }
   
   public static function input_hidden($name,$value){
       return self::_input('hidden',$name,$value);
   }
   
   public static function input_file($name,$params=""){
      return self::_input('file',$name,"",$params);
   }
   
   public static function input_password($name,$value='',$params=""){
      return self::_input('password',$name,$value,$params);
   }
   
   public static function input_image($src,$params=""){
      return self::_input("image",'',$label,$params,array('src'=>$src)); 
   }
   
   public static function input_button($label,$params=""){
      return self::_input("button",'',$label,$params); 
   }
   
   public static function input_submit($label='',$params=""){
      return self::_input("submit",'',$label,$params); 
   }
   
   public static function submit($label="",$params=""){
      return self::input_submit($label,$params);
   }
   
   
   public static function input_reset($label,$params=''){
      return self::_input("reset",'',$label,$params); 
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
    
   
   private static function _input($type='text',$name='',$value='',$param="",$paramMore=''){
       $_param=array();
       if($name){
          $_param['name']=$name;
          $_param['id']=self::getIDByName($name);
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
   
}