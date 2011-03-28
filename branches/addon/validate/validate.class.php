<?php
/**
 * 表单验证
 */
require_once dirname(__FILE__).'/type.class.php';
require_once dirname(__FILE__).'/rule.class.php';

class rValidate{
  private $config;
  private $validate=true;
  private $errors=array();
  private $errorDetails=array();
  
  public function __construct($config){
     if(!is_array($config)){
          throw new Exception("config must be array");
      }
      $this->config=$config;
  }
  
  /**
   * 进行验证
   * @param array $data 待验证的数据
   */
  public function validate($data){
    $this->validate=true;
    foreach ($this->config as $name=>$param){
      $value=isset($data[$name])?$data[$name]:null;
      foreach ($param['rule'] as $k=>$v){
          $fn=$k;//规则名称
          $_param=array($value);
         if(is_int($k)){
            $fn=$v;
          }else{
             if(is_array($v)){
                 $_param=array_merge($_param,$v);
             }else{
                 $_param[]=$v;
               }
             $_param[]=$data;
           }
          if(!call_user_func_array(array('rValidate_rule',$fn), $_param)){
             $this->validate=false;
             $this->errors[$name]=$param['msg'];
             $this->errorDetails[$name][$fn]=isset($_param[1])?$_param[1]:'';
           }
       }
    }
    return $this->validate;
  }
  
  /**
   * 获取错误信息
   * @return array 
   */
  public function getErrors(){
    return $this->errors;
  }
  
  /**
   * 获取html格式化的错误详情 使用ul li label包裹
   * @return string 
   */
  public function getErrorsAsHtml(){
    if(!$this->errors)return '';
    $html="<ul>";
    foreach ($this->errors as $name=>$msg){
      $html.="<li><label for='{$name}'>{$msg}</label></li>";
    }
    return $html."</ul>";
  }
  
  /**
   * 获取错误的详情  字段名称 对应 验证规则数组
   * 可以知道是那条规则没有通过验证
  * $errors => Array (1)
   * (
  *    [  title  ] => Array (2)
   *    (
  *    |    [  0  ] = String(8) "required"
  *    |    [  1  ] = String(5) "email"
   *    )
   * )
   * @param string $name
   * @return array
   */
  public function getErrorDetail($name=null){
    return $name?(isset($this->errorDetails[$name])?$this->errorDetails[$name]:array()):$this->errorDetails;
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
        foreach ($subConfig['rule'] as $k=>$v){
           if(is_int($k)){
             $jsRule['rules'][$name][$v]=true;
            }else{
             $jsRule['rules'][$name][$k]=$v;
            }
             $jsRule['messages'][$name]=$subConfig['msg'];
         }
     }
     
     return $json?json_encode($jsRule):$jsRule;
  }
  
}