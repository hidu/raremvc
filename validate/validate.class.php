<?php
class rValidate{
  private $config;
  private $validate=true;
  
  public function __construct($config){
     if(!is_array($this->config)){
          throw new Exception("config must be array");
      }
     $this->config=$this->fixRule($this->config);
  }
  
  /**
   * 进行验证
   * @param array $data 待验证的数据
   */
  public function validate($data){
    $this->validate=true;
  }
  
  public function getErrors(){
  
  }
  
  public function getErrorsAsHtml(){
  
  }
  
  protected function fixRule($config){
    return $config;
  }
}