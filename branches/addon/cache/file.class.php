<?php
/**
 *@copyright rareMVC
 *文件缓存 
 * @author duwei
 *
 */
class rCache_file extends rCache{
    protected  $cacheBath;
    protected $subDirName;
    public function __construct($dbName="",$cacheMod='app'){
       $this->cacheBath=($cacheMod=='root'?dirname(RARE_CACHE_DIR):RARE_CACHE_DIR)."/file/".($dbName?$dbName."/":"");
    }
    
    public function has($key){
        $file=$this->getCacheFilePath($key);
        if(!file_exists($file))return false;
         $handle=@fopen($file,'r');
        if(!$handle){
            throw new Exception("({$file})file can not read", 501);
         }
         fgets($handle);
         $lifeLine=fgets($handle);
         fclose($handle);
         $life=(int)str_replace("//", "", $lifeLine);
         return !$life || $life>=time();
    }
    public function get($key,$default=null){
         $data=file_get_contents($this->getCacheFilePath($key));
         $data=substr($data,strpos($data, "\n",strpos($data,"\n")+1)+1);
         return $data===false?$default:$data;
    }
    public function set($key,$data,$lifetime=0){
       $file=$this->getCacheFilePath($key);
       directory(dirname($file));
       if($lifetime)$lifetime+=time();
       file_put_contents($file, "\n//{$lifetime}\n".$data,LOCK_EX);
       clearstatcache();
       return true;
    }
    public function remove($key){
       @unlink($this->getCacheFilePath($key));
    }
    public function getCacheFilePath($key){
        return $this->cacheBath.$this->subDirName.$key.".php";
    }

}