<?php
/**
 * 表单验证
 * 可以对该功能进行扩展，只需要新建一个类为 custom_Validate_rule，格式和rValidate_rule一样即可添加新规则
 * 具备数据过滤功能，可以使用getFormData方法来获取通过验证的数据
 * 该验证类只支持一维数组
 * @author duwei
 * @package rara Addon
 */
require_once dirname(__FILE__).'/type.class.php';
require_once dirname(__FILE__).'/rule.class.php';

class rValidate{
  private $config;
  private $validate=true;
  private $errors=array();
  private $formData=array();
  
  public function __construct($config){
      if(is_string($config)){
          $app=rareContext::getContext();
          $configPath=$app->getModuleDir().$app->getModuleName()."/config/validate/".$config.".php";
          $config=require $configPath;
       }
     if(!is_array($config)){
          throw new Exception("config must be array");
      }
      $this->config=$config;
      //将配置标准化
      foreach($this->config as $name=>$rules){
        if(!is_array($rules))$rules=array();
        
        foreach ($rules as $ruleName=>$v){
            if(is_int($ruleName)){
              $rules[$v]=array(true);
              unset($rules[$ruleName]);
             }else{
               if(!is_array($v)){
                 $rules[$ruleName]=array($v);
                }
              }
         }
         $this->config[$name]=$rules;
      }
  }
  /**
   * 设置字段默认的信息,如label和默认错误信息
   * @param string $fieldName
   * @param string $label
   * @param string $defaultMsg
   */
  public function setField($fieldName,$label,$defaultMsg=null){
     $this->config[$fieldName][rValidate_type::Info]=array('label'=>$label);
     if(!is_null($defaultMsg)){
       $this->config[$fieldName][rValidate_type::Info]['msg']=$defaultMsg;
     }
  }
  
  /**
   * 动态添加规则
   * @param string $fieldName
   * @param string $ruleName
   * @param mixed $ruleParam
   * @param string $msg
   * @example
   * $validate->setRule('paasword',rValidate_type::Rangelength,array(6,20),"密码的长度限制在6-20位，您的密码长度是{length}!");
   * 注意，若是一个之前不再规则中定义的字段，需要使用setField 声明字段
   */
  public function setRule($fieldName,$ruleName,$ruleParam=true,$msg=null){
    $this->config[$fieldName][$ruleName][0]=$ruleParam;
    if(!is_null($msg)){
      $this->config[$fieldName][$ruleName][1]=$msg;
    }
  }
  
  public function removeRule($fieldName,$ruleName){
    if(isset($this->config[$fieldName]) && isset($this->config[$fieldName][$ruleName])){
      unset($this->config[$fieldName][$ruleName]);
    }
  }
  
  
  /**
   * 进行验证
   * @param array $data 待验证的数据
   */
  public  function validate($data){
    $this->validate=true;
    $this->formData=array();
    print_r($this->config);
    
    foreach ($this->config as $name=>$rules){
      $value=isset($data[$name])?(is_array($data[$name])?$data[$name]:trim($data[$name])):null;
      $this->formData[$name]=$value;
      
     $hasValue=is_array($value)?count($value):strlen($value);//是否有值  长度大于0
      
     //当规则有必填项验证时,进行必填项验证
     if(array_key_exists(rValidate_type::Required,$rules)){
        if(!rValidate_rule::required($value)){
             $this->validate=false;
             $this->errors[$name][rValidate_type::Required]=$this->getFieldError($name, rValidate_type::Required, $value);
         }
     }else if(!$hasValue){  //当规则无必填项 且值为空时不进行验证
        continue;
      }
      
      foreach ($rules as $ruleName=>$paramValue){
           if(isset($this->errors[$name]) && array_key_exists(rValidate_type::Required,$this->errors[$name]))continue;
           if($ruleName == rValidate_type::Required || $ruleName==rValidate_type::Info)continue;
           
           $_param=array($value);
           $_param[]=$paramValue[0];  //array or int|string|....    
           $_param[]=$data;       //最后一个参数为当前验证的所有的值 for fn like smallThan($value,$to,$all)
           
          if(class_exists("custom_Validate_rule",true) && method_exists("custom_Validate_rule", $ruleName)){
             $validClass=array('custom_validate_rule',$ruleName);
           }else{
             $validClass=array('rValidate_rule',$ruleName);
             }
          if(!call_user_func_array($validClass, $_param)){
             $this->validate=false;
             $this->errors[$name][$ruleName]=$this->getFieldError($name, $ruleName, $value);
           }
       }
    }
    
    return $this->validate;
  }
  
  public function isValidate(){
     return $this->validate;
  }
  
  /**
   * 获取通过验证了的有验证规则数据
   * @return array
   */
  public function getFormData(){
    return $this->formData;
  }
  
  /**
   * 获取错误信息
   * @return array 
   */
  public function getErrors(){
    return $this->errors;
  }
  
  /**
   * 给指定的字段添加一个错误提示
   * @param string $name
   * @param string $error
   */
  public function addError($name,$error){
    $this->validate=false;
    $this->errors[$name][]=$error;
  }
  
  /**
   * 获取html格式化的错误详情 使用ul li label包裹
   * @return string 
   */
  public function getErrorsAsHtml($fieldPrex=''){
    if(!$this->errors)return '';
    $html="<dl>";
    foreach ($this->errors as $name=>$msg){
      $label=$this->config[$name][rValidate_type::Info]['label'];
      $html.="<dt>{$label}</dt>";
      foreach ($msg as $fn=>$errorMsg){
        $html.="<dd><label for='{$fieldPrex}{$name}'>{$errorMsg}</label></dd>";
       }
    }
    return $html."</dl>";
  }
  
  public function getErrorsAsString(){
    if(!$this->errors)return '';
    $msgs=array();
    foreach ($this->errors as $name=>$msg){
       $msgs=array_merge($msgs,array_values($msg));
     }
     return implode("\n", $msgs);
  }
  
  
  /**
   * 返回 jQuery Validation Plugin 支持的验证规则
   * jQuery Validation Plugin：
   *     http://bassistance.de/jquery-plugins/jquery-plugin-validation/
   *     http://docs.jquery.com/Plugins/Validation
   *     
   * @param boolean $json
   */
  public function getJsRule($json=false){
    $jsRule=array();
    foreach($this->config as $name=>$subConfig){
        foreach ($subConfig as $ruleName=>$param){
             $jsRule['rules'][$name][$ruleName]=$param[0];
             $jsRule['messages'][$name]=$this->getFileRuleMsg($name, $ruleName);
         }
     }
     
     return $json?json_encode($jsRule):$jsRule;
  }
  
  
  /**
  * 获取指定字段的验证后的错误信息
  * @param string $fieldName
  * @param string $ruleName
  * @param mixed $value
  * @return string
  */
  public function getFieldError($fieldName,$ruleName,$value){
    if(!isset($this->config[$fieldName])||!isset($this->config[$fieldName][$ruleName]))return '';
    $msg=$this->getFileRuleMsg($fieldName, $ruleName);
    $label=$this->config[$fieldName][rValidate_type::Info]['label'];
    return  str_replace(array("{value}","{length}","{label}"),array($value,is_array($value)?count($value):mb_strlen($value),$label), $msg);
  }
  
  /**
   * 获取字段配置的错误提示信息
   * @param string $fieldName
   * @param string $ruleName
   */
  private function getFileRuleMsg($fieldName,$ruleName){
    return isset($this->config[$fieldName][$ruleName][1])?$this->config[$fieldName][$ruleName][1]:$this->config[$fieldName][rValidate_type::Info]['msg'];
  }
  
}