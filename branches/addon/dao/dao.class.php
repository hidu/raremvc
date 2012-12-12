<?php
/**
 * rare.hongtao3.com
 * @author duwei
 * @since 2012-04-03
 * @example
 * $dao=rDao::getInstance('article', 'articleid');
 * 获取指定条件下的最小值:
 * $min_articleid=$dao->min(null,"where articleid>?",5);
 * 获取articleid=5的title字段
 * $title=$dao->getOneField('title', 'where articleid=?',5);
 * @package addon\dao
 */
final class rDao extends rDao_base{
  private static $instances=array();
  
  public function __construct($table_name,$key_field,$dbConfigName=null){
    $this->table_name=$table_name;
    $this->key_field=$key_field;
    $this->dbConfigName=$dbConfigName;
    parent::__construct();
  }
  
  public function init(){}
  
  /**
   * 
   * @param string $table_name
   * @param string $key_field
   * @param string $dbConfigName
   * @return rDao
   */
  public static function getInstance($table_name,$key_field,$dbConfigName=null){
      $instance_name=$table_name.$key_field.$dbConfigName;
      if(!isset(self::$instances[$instance_name])){
        self::$instances[$instance_name]=new self($table_name, $key_field,$dbConfigName);
      }
      return self::$instances[$instance_name];
  }
}