<?php
/**
 * 使用sqlite数据库进行缓存处理
 * @author duwei
 */
class rCache_sqlite extends rCache{
    private $db;
    
    /**
     * @param string $cacheMod 缓存级别 默认为当前全局
     */
    public function __construct($cacheMod='root'){
        $filename=$cacheMod=='app'?RARE_CACHE_DIR:dirname(RARE_CACHE_DIR)."/";
        
        $this->db=new PDo("sqlite:".$filename."cache.sqlite");
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $q=@$this->db->query("select * from cache limit 1");
        if($q==false){
         $this->db->exec("create table cache(id varchar(255),data text,life int);CREATE UNIQUE INDEX [cache_unique] ON cache ([key])"); 
        }
        if(mt_rand(0, 100)==50){
           $this->db->exec("delete from cache where life>0 and life<".time());
        }
    }   
  
    public function has($key){
       $sth=$this->db->prepare("select id from cache where id=? and (life>? or life is NULL)");
       $sth->execute(array($key,time()));
       $one=$sth->fetch();
       return (boolean)$one;
    }
    
    public function get($key,$default=null){
      $sth=$this->db->prepare("select data from cache where id=?");
      $sth->execute(array($key));
      $one=$sth->fetchColumn();
      if(false!==$one)return $one;
      return $default;
    }
    
    public function set($key, $data,$lifetime=null){
      if(!is_null($lifetime))$lifetime+=time();
      $sth=$this->db->prepare("insert or replace into cache(id,data,life) values(?,?,?)");
      return  $sth->execute(array($key,$data,$lifetime));
    }
    
    public function remove($key){
      $sth=$this->db->prepare("delete from cache where id=?");
      return $sth->execute(array($key));
    }
    
    public function removeAll(){
      return $sth=$this->db->exec("delete from cache");
    }
    
    public function getBackend(){
      return $this->db;
    }
    
    public function getByLike($keyLike){
       $sth=$this->db->prepare("select data from cache where id like '?'");
       $sth->execute(array($keyLike));
       return $sth->fetchAll();
    }
    
}