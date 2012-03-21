<?php
class dao_article extends dao_base{
  public function __construct(){
     $this->tableName="article";
     $this->keyField="articleid";
  }
}