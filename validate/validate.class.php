<?php
/**
 * 表单验证
 * 可以对该功能进行扩展，只需要新建一个类为 custom_Validate_rule，格式和rValidate_rule一样即可添加新规则
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
  
  public function __construct($config,$data){
      if(is_string($config)){
          $app=rareContext::getContext();
          $configPath=$app->getModuleDir().$app->getModuleName()."/config/validate/".$config.".php";
          $config=require $configPath;
       }
     if(!is_array($config)){
          throw new Exception("config must be array");
      }
      $this->config=$config;
      $this->validate($data);
  }
  
  /**
   * 进行验证
   * @param array $data 待验证的数据
   */
  protected  function validate($data){
    $this->validate=true;
    $this->formData=array();
    
    foreach ($this->config as $name=>$rules){
      $value=isset($data[$name])?trim($data[$name]):null;
      $this->formData[$name]=$value;
      
     foreach ($rules as $ruleName=>$v){
           //for $rule['consignee'][rValidate_type::Required]  ="请填写收货人姓名！";
           if(!is_array($v)){
                $rules[$ruleName]=array(true,$v);
             }
          $this->config[$name]=$rules;
      }
      
      foreach ($rules as $ruleName=>$v){
           if(isset($this->errors[$name]) && array_key_exists(rValidate_type::Required,$this->errors[$name]))continue;
             
           $ruleParam=$v[0];  //array or int|string|....
           $msg=$v[1];
           
           $_param=array($value);
           $_param[]=$ruleParam;        
           $_param[]=$data;       //最后一个参数为当前验证的所有的值 for fn like smallThan($value,$to,$all)
           
          if(class_exists("custom_Validate_rule",true) && method_exists("custom_Validate_rule", $ruleName)){
             $validClass=array('custom_validate_rule',$ruleName);
           }else{
             $validClass=array('rValidate_rule',$ruleName);
             }
          if(!call_user_func_array($validClass, $_param)){
             $this->validate=false;
             $this->errors[$name][$ruleName]=str_replace(array("{value}","{length}"),array($value,is_array($value)?count($value):mb_strlen($value)), $msg);
           }
       }
    }
  }
  
  public function isValidate(){
     return $this->validate;
  }
  
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
  
  public function addError($name,$error){
    $this->validate=false;
    $this->errors[$name][]=$error;
  }
  
  /**
   * 获取html格式化的错误详情 使用ul li label包裹
   * @return string 
   */
  public function getErrorsAsHtml(){
    if(!$this->errors)return '';
    $html="<dl>";
    foreach ($this->errors as $name=>$msg){
      $label=isset($this->config[$name][rValidate_type::Label])?$this->config[$name][rValidate_type::Label][1]:"";
      $html.="<dt>{$label}</dt>";
      foreach ($msg as $fn=>$errorMsg){
        $html.="<dd><label for='{$name}'>{$errorMsg}</label></dd>";
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
             $jsRule['messages'][$name]=$param[1];
         }
     }
     
     return $json?json_encode($jsRule):$jsRule;
  }
  
}