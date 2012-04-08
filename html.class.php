<?php
/**
 * @copyright rareMVC
 * @author duwei
 * html表单输出工具类
 */
class rHtml{
  
   private static $autoID=true;
   
   private static $autoClass=true;
   /**
    * 是否允许自动添加id字段
    * @param boolean $enable
    */
   public static function enableAutoID($enable){
       self::$autoID=$enable;
   }
   /**
    * 是否允许自动添加统一的class
    * 如input-text添加r-text
    * @param boolean $autoClass
    */
   public static function enableAutoClass($autoClass){
       self::$autoClass=$autoClass;
   }
    /**
    * 检查select radio_group checkbox_group的 选项
    * 如 选项为 array(3,5,8) 一般期望为 其key 和value同样的值
    * @param array $options
    */
   public static function options($options){
     $tmp=array_values($options);
     if($tmp==$options){
       $tmp=array();
       foreach ($options as $option){
          $tmp[$option]=$option;
        }
        return $tmp;
      }
      return $options;
   }
    
   public static function select($name,$value,$options,$params=''){
       $params=self::_paramMergeWithClass(array(), $params,$name,'select');
       $html="<select ".self::_paramMerge($params,true).">";
       if(!is_array($value))$value=explode(",", $value);
       $value=array_flip($value);
       foreach ($options as $_k=>$_v){
           $html.="<option value=\"".self::h($_k)."\"".(array_key_exists($_k, $value)?' selected="selected"':"").">".self::h($_v)."</option>";
        }
        return $html."</select>";
   }
   
  
   
   public static function textArea($name,$value='',$params=''){
     $params=self::_paramMergeWithClass(array(), $params,$name,'textarea');
     return '<textarea '.self::_paramMerge($params,true).">".self::h($value)."</textarea>";
   }
   
   
   public static function radio($name,$customValue,$itemValue,$params=""){
       $_param=array();
       if($customValue===true)$customValue=$itemValue;
       if(strcmp($customValue, $itemValue)==0)$_param['checked']='true';
      return self::inputTag('radio',$name,$itemValue,$params,$_param);
   }
   
   /**
    * 
    * @param string $name
    * @param string $value  
    * @param array $options
    * @param string|array $params
    */
   public static function radio_group($name,$value,$options,$params=""){
       $html="<span class='r-radio-group'>";
       foreach ($options as $_k=>$_v){
           $_param=array();
           if(strcmp($_k, $value)==0)$_param['checked']="checked";
           $_param['id']='';
           $html.="<label>".self::inputTag('radio',$name,$_k,$params,$_param).self::h($_v)."</label>";
        }
        $html.="</span>";
        return $html;
   }
   
   public static function checkbox_group($name,$value,$options,$params=''){
      if(!is_array($options))$options=array($options=>'');
      if(!is_array($value))$value=explode(",",$value);
      $html="<span class='r-checkbox-group'>";
      $params=self::_paramMerge($params);
      foreach ($options as $_k=>$_v){
          $_param=array();
          if(in_array($_k, $value))$_param['checked']="checked";
          if($params['id']){
             $_param['id']=$params['id']."_".$_k;
           }else if(self::$autoID){
             $_param['id']=self::getIDByName($name)."_".$_k;
           }
          $html.="<label>".self::inputTag('checkbox',$name,$_k,$params,$_param)."{$_v}</label>";
       }
        $html.="</span>";
      return $html;
   }
   
   /**
    * 
    * @param string $name
    * @param string $customValue  用户输入的值，可能是数据库读取的
    * @param string $itemValue 当前item的值
    * @param string|array $params
    */
   public static function checkbox($name,$customValue,$itemValue,$params=''){
       $_param=array();
       if($customValue===true)$customValue=$itemValue;
       if(strcmp($customValue, $itemValue)==0)$_param['checked']='true';
       return self::inputTag('checkbox',$name,$itemValue,$_param,$params);
   }
   
   public static function input($name,$value="",$params=""){
     return self::inputTag('text',$name,$value,$params);
   }
   
   public static function input_hidden($name,$value,$params=""){
       return self::inputTag('hidden',$name,$value,$params);
   }
   
   public static function hidden($name,$value,$params=""){
       return self::inputTag('hidden',$name,$value,$params);
   }
   
   public static function input_file($name,$params=""){
      return self::inputTag('file',$name,"",$params);
   }
   
   public static function password($name,$value='',$params=""){
      return self::inputTag('password',$name,$value,$params);
   }
   
   public static function input_image($src,$params=""){
      return self::inputTag("image",'','',$params,array('src'=>$src)); 
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
   public static function input_email($name,$value,$params=''){
      return self::inputTag('email',$name,$value,$params);
   }
   
   /**
    * html5
    * @param string $name
    * @param mix $params
    */
   public static function input_search($name,$value,$params=''){
      return self::inputTag('search',$name,$value,$params);
   }
   
   /**
    * html5
    * @param string $name
    * @param mix $params
    */
   public static function input_url($name,$value,$params=''){
      return self::inputTag('url',$name,$value,$params);
   }
   
   
   public static function h($value){
       return htmlspecialchars($value,ENT_QUOTES);
   }
   
   public static function getIDByName($name){
        return trim(str_replace(array("][","[","]"),array("_","_",""),$name),"_");
    }
    
    public static function a($url,$text,$params=''){
         if(!rare_isUrl($url) && !rare_strStartWith($url, '#') && !rare_strStartWith($url, "javascript:")){
           $url=url($url);
         }
        return '<a href="'.self::h($url).'"'.self::_paramMerge($params,true).">".self::h($text)."</a>";
    }
    
    public static function js_alertGo($message,$url){
        $go=is_int($url)?"history.go($url)":"location.href='{$url}'";
        echo'<script>'.(strlen($message)?'alert("'.self::h(addcslashes($message,"\n\r")).'");':'').$go.';</script>';
        die;
    }
    
    /**
     * 
     * @param mixed $params1
     * @param mixed $params2
     * @param string $name
     * @param string $type
     * @return array
     */
    private static function _paramMergeWithClass($params1,$params2,$name,$type){
       $param=self::_paramMerge($params1, $params2);
       if(self::$autoClass){
          $param['class']="r-".$type.(isset($param['class'])?" ".$param['class']:"");
         }
        
      if($name){
          $param['name']=$name;
          if(self::$autoID && !array_key_exists("id", $param)){
             $param['id']=self::getIDByName($name);
            }
        }
        return $param;
     }
   
   public static function inputTag($type='text',$name='',$value='',$param="",$paramMore=''){
        $param=self::_paramMergeWithClass($param, $paramMore, $name,$type);
        $paramStr=self::_paramMerge($param,true);
     return "<input type=\"{$type}\" value=\"".self::h($value)."\"{$paramStr}/>";
   }
   
    
    private static function  _paramMerge(){
         $numargs = func_num_args();
         $param=array();
         for($i=0;$i<$numargs;$i++){
            $_param=func_get_arg($i);
            if($numargs-1==$i && $_param===true)continue;//最后一个参数为true,将所有数组按照字符串返回
           if(is_string($_param))$_param=sfToolkit::stringToArray($_param);
           if(!is_array($_param))$_param=array();
           $param=array_merge($param,$_param);
          }
         if($_param===true){
               $str="";
              foreach ($param as $_k=>$_v){
                    if(is_null($_v) || !strlen($_v))continue;
                    $str.=$_k.'="'.self::h($_v).'" ';
                 }
               return $str?" ".trim($str):"";
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
    
    /**
     * 使用post将数据提交到指定的地址
     * @param string $url  提交的action 地址
     * @param array $params  提交的参数
     * @param string $charset 提交目标的编码
     */
    public static function post2Url($url,$params=array(),$charset="utf-8"){
       @ob_end_clean();
       @ob_clean();
       self::enableAutoID(false);
       self::enableAutoClass(false);
       header("Content-Type:text/html; charset=utf-8"); 
       $html="<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /></head>".
             "<body onload='document.form1.submit()'>";
       
       //fix ie charset
       $charset4ie=strtoupper($charset)=='utf-8'?"":"<script>if(top.execScript){document.charset=\"$charset\";}</script>";
       $html.=$charset4ie;
       
       $html.="<form action='{$url}' method='post' name='form1' accept-charset='{$charset}'>";
       foreach ($params as $k=>$v){
          $html.=self::hidden($k, $v);
        }
       $html.="</form>";
       $html.="</body></html>";
       echo $html;
       die;
    }
    
    
    
     /**
     * 压缩html 代码 取出换行符，回车符和多余空白
     * 
     * 该函数会有几ms的时间消耗，但是压缩为一行的代码兼容性更好（空格符、换行符的表现）
     * 书写的html代码需要满足以下条件:
     *  1.javascript代码中不能使用单行注释
     *  2.javascript每句完成后添加；
     * @param string $html
     * @param boolean $pre  是否支持pre标签
     */
   public static function reduceSpace($html,$pre=false){
     $pattern=array();
     $replacement=array();
     if($pre){
       $matches=array();
       $pre="#\s?<pre[^>]*?>.*?</pre>\s?#si";
       preg_match_all($pre, $html, $matches);
       $preTmp="_".uniqid('pre')."_";
       $pattern[]=$pre;
       $replacement[]=$preTmp;
     }
     $pattern+=array( 
                      "/\n|\r/",
                      "/\s+/",
                      "/>\s+</",
                      "/\s+</",
                      "/>\s+/"
                      );
      $replacement+=array(
                         "",
                         " ",
                         "><",
                         "<",
                         ">"
                         );
      $html=preg_replace($pattern, $replacement, $html);
      
      if($pre && isset($matches[0]) && count($matches[0])){
        $p2=array();
        $p2=array_fill(0, count($matches[0]), "/".$preTmp."/");
        $html=preg_replace($p2, $matches[0], $html,1);
        unset($p2);
        unset($matches);
      }
      return $html;
   }
   
   
   /**
    * 将url地址重新进行url_encode
    * @param string $url
    * @param string $charset 将参数进行编码转换
    */
   public static function url_encode($url,$charset=null){
      $url_info=parse_url($url);
      if(!isset($url_info['query']))return $url;
      
      $defaultCharset=rareConfig::get('charset');
      parse_str($url_info['query'],$params);
      if($charset && $charset !=$defaultCharset){
        foreach ($params as $k=>$v){
            $params[$k]=mb_convert_encoding($v, $charset,$defaultCharset);
         }
      }
      $url=isset($url_info['scheme'])?$url_info['scheme']."://".$url_info['host']:"";
      $url.=$url_info['path']."?".http_build_query($params);
      $url.=isset($url_info['fragment'])?"#".$url_info['fragment']:"";
      return $url;
   }
   
   public static function button_link($text,$url,$confirm=null,$params=null){
     if(!rare_isUrl($url)){
       $url=url($url);
      }
      if(!empty($confirm)){
         $confirm="if(!confirm('".addslashes($confirm)."'))return false;";
       }
       $_param=self::_paramMerge(array('onclick'=>$confirm."location.href='".$url."'","class"=>'r-button-link'),$params);
       return self::input_button($text,$_param);
   }
   
}
