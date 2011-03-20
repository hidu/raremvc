<?php
/**
 * 数据库操作
 * 一个简单的sql模板类，只是对pdo的简单封装,主从模式，
 * 读操作统一使用从服务器，写操作统一使用主服务器。
 * 
 *@copyright rareMVC
 *@author duwei
 *
 *数据库配置文件存放在
 *1.单个应用的数据库配置              /appDir/config/db.php
 *2.多个程序公用的数据库配置文件   /lib/config/db.php
 *多存在两个配置文件，配置文件将会进行合并，app的配置文件将会覆盖公用配置的相同项目
 *
 *一个数据库配置文件:
 <?php
//数据库配置文件
//多数据库配置，主从模式
$db=array();
$i="myDb";
$db[$i]['master']['dsn']="mysql:host=127.0.0.1; port=3306; dbname=myDb";
$db[$i]['master']['username']="user";
$db[$i]['master']['passwd']="psw";
$db[$i]['slave']['dsn']="mysql:host=192.168.2.1; port=3306; dbname=myDb";
$db[$i]['slave']['username']="user";
$db[$i]['slave']['passwd']="psw";
return $db;
?>
 */
class rDB{
     public static $sqls=array();
     public static $pageLabel="p";//分页参数名称
     protected  static $defaultDbName="default";//默认数据库
     
     /**
      * 获取一个PDO对象
      * @param string $dbName 数据库名称 若 $dbName="myDbName.[master|slave]",则$dbName="myDbName" $type=[master|slave]
      * @param string $type   类型 [master|slave] 主从
      * @return PDO
      */
    public static function getPdo($dbName=null,$type='master'){
         if($dbName && is_object($dbName))return $dbName;
         static $dbhs=array();
         if(!$dbName)$dbName=self::$defaultDbName;
         if(strpos(".", $dbName)){
            $tmp=explode(".", $dbName);
            if(count($tmp)==2 && in_array($tmp[1], array("master",'slave'))){
              $dbName=$tmp[0];
              $type=$tmp[1];               
            }
          }
         $key=$dbName.".".$type;
         if(!isset($dbhs[$key])){
             $configs=self::getConfigByDbName($dbName);
             $config=isset($configs[$type])?$configs[$type]:null;
             if(!$config)throw Exception("undefined dbConfig ".$type);
             if(!isset($config['encode']))$config['encode']="utf8";
             $dbh=new PDO($config['dsn'], $config['username'], $config['passwd'], array());
             $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, rareConfig::get('db_fetch_mode',PDO::FETCH_ASSOC));
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
             $dbh->exec("SET NAMES {$config['encode']}");
             $dbhs[$key]=$dbh;
         }
         return $dbhs[$key];
    }
    
    /**
     * 获取指定的数据库配置情况
     * @param string $dbName
     */
    public  static function getConfigByDbName($dbName=null){
      static $config=null;
        if($config==null){
           $dbConfig=rareConfig::getAll('db');
           $config=self::init_config($dbConfig);
           if(count($config)==1 && !isset($config[self::$defaultDbName])){
               $_first=each($config);
               $config[self::$defaultDbName]=$_first['value'];
            }
           $rootDbConfigFile=rareContext::getContext()->getRootLibDir()."config/db.php";
           if(file_exists($rootDbConfigFile)){
              $shareConfig=require $rootDbConfigFile;
              if(is_array($shareConfig)){
                  $shareConfig=self::init_config($shareConfig);
                  $config=array_merge($shareConfig,$config);
                }
            }
        }
        if($dbName==null)$dbName=self::$defaultDbName;
       return $config[$dbName];
    }
    
    /**
     * 修正数据库配置，格式化为标准的master,slave格式的数据库配置
     * @param array $dbConfig
     */
    protected  static function init_config($dbConfig){
         $config=array();
         if(isset($dbConfig['dsn']) && is_string($dbConfig['dsn'])){
              $config['default']['master']=$dbConfig;
          }else{
            foreach ($dbConfig as $_key=>$value){
                if(isset($value['dsn']) && is_string($value['dsn'])){
                   $config[$_key]['master']=$value;
                }else{
                   foreach ($value as $_k=>$_v){
                     if(in_array($_k, array('master','slave')) && isset($_v['dsn']) && is_string($_v['dsn'])){
                           $config[$_key][$_k]=$_v;
                         }
                      }
                  }
             }
           }
          foreach ($config as $_sourceID=>$dbs){
             if(!isset($dbs['slave'])){
                   $config[$_sourceID]['slave']=$dbs['master'];
               }
              if(isset($dbs['slave']['dsn']) && !isset($dbs['slave']['username'])){
                $config[$_sourceID]['slave']['username']=$dbs['master']['username'];
                $config[$_sourceID]['slave']['passwd']=$dbs['master']['passwd'];
               }
           }
       return $config;
    }
    
    /**
     * 查询出所有的记录
     * @param string $sql
     * @param array $params
     * @param string $dbName
     */
    public static function execQuery($sql,$params=array(),$dbName=null){
        $pdo=self::getPdo($dbName,'slave');
        self::_paramParse($sql, $params);
        $sth=self::_preExec($pdo, $sql, $params);
        return $sth;
    }
    
    /**
     * 查询出所有的记录
     * @param string $sql
     * @param array $params
     * @param string $dbName
     */
    public static function queryAll($sql,$params=array(),$dbName=null){
        return self::execQuery($sql, $params,$dbName)->fetchAll();
    }
    /**
     * 查询出一条记录
     * @param string $sql
     * @param array $params
     * @param string $dbName
     */
    public static function query($sql,$params=array(),$dbName=null){
      return self::execQuery($sql, $params,$dbName)->fetch();
    }
    
    /**
     * 分页查询
     * 如
     * list($list,$pager)=rareDb::listPage("select * from artilce where cateID=? and createTime>?",array(1,date('Y-m-d H:i:s')),20);
     * $list是一个数组，为我们查询的数据
     * $pager  为一个 rarePager 对象，以实现__toString方向，可以在模板中直接输出 
     *     如<?php echo $pager->setlinkNum(5);//每页显示5个链接，并输出?>
     *  当前只实现了mysql 分页查询，其他数据库可以使用hook功能来实现
     *  <?php
     *  class myRareDb{
     *      public static function mssql($sql,$params=array(),$size=10,$dbName=null){
     *          //@todo
     *          return array($resultList,$totleNum);
     *        }
     *  }?>   
     * @param string $sql
     * @param array $params
     * @param int $size
     * @param string $dbName
     * @return array     ($list,$pager) 
     */
    public static function listPage($sql,$params=array(),$size=10,$dbName=null){
        $page=isset($_GET[self::$pageLabel])?(int)$_GET[self::$pageLabel]:1;
        $page=$page>0? $page:1;
        $sql=trim($sql);

        $list=array();
        $total=0;
        
        $pdo=self::getPdo($dbName,'slave');
        $driver_name=strtolower($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        if($driver_name=="mysql"){
          $start= $size*($page-1);
          $limit= $start.','.$size;
          $sql=preg_replace("/^select/i", "SELECT SQL_CALC_FOUND_ROWS ", $sql)." LIMIT ".$limit;
          $list=self::queryAll($sql, $params,$dbName);
          $sql2='SELECT FOUND_ROWS() as c;';
          $totalInfo=self::query($sql2,array(),$dbName);
          $total=(int)$totalInfo['c'];
         }else{
            $fn="listPage_".$driver_name;
            if(class_exists('myRareDb',true) && method_exists('myRareDb',$fn)){
                list($list,$total)=myRareDb::$fn($sql,$params=array(),$size=10,$dbName=null);
              }
         }            
        $pageInfo=array();
        $pageInfo['page']=$page;
        $pageInfo['size']=$size;
        $pageInfo['total']=$total;
        return array($list,new rPager($pageInfo));
     }
     
    /**
     * 
     * @param string $sql
     * @param array $params
     * @param string $dbName
     * @return int
     */
    public static function exec($sql,$params=array(),$dbName=null){
        $pdo=self::getPdo($dbName,'master');
        self::_paramParse($sql, $params);
        $sth=self::_preExec($pdo, $sql, $params);
        return $sth->rowCount();
    }
    
    private static function _preExec($pdo,$sql,$params){
       try{
          $sth=$pdo->prepare($sql);
          $sth->execute($params);
          self::_log($sql, $params);
        }catch (Exception $e){
            throw new Exception($e->getMessage()."\nsql:\n".$sql."\ndata:\n".print_r($params,true)."\n", $code, $previous);
          }
      return $sth;
    }
    
    public static function insert($sql,$params=array(),$dbName=null){
        $pdo=self::getPdo($dbName);
        $sth=self::_preExec($pdo, $sql, $params);
        return $pdo->lastInsertId();
    }
    
    /**
     * 将数据插入到指定表中
     * @param string $tableName
     * @param string $data  要insert到表中的数据
     * @param string $dbName
     */
    public static function table_insert($tableName,$data,$dbName=null){
         self::_dataFilter($tableName, $data);
         $sql="insert into `{$tableName}`(".join(",", array_keys($data)).") values(".rtrim(str_repeat("?,", count($data)),",").")";
         return self::insert($sql, array_values($data)); 
    }
    
    /**
     * 对指定表进行更新操作
     * rareDb::table_update('tableName',array('title'=>'this is title','content'=>'this is content'),'id=?',array(12));
     * @param string $tableName
     * @param array $data   要进行更新的数据  array('title'=>'this is title','hitNum=hitNum+1')
     * @param string $where
     * @param string $whereParam
     * @param string $dbName
     */
    public static function table_update($tableName,$data,$where,$whereParam=array(),$dbName=null){
         self::_dataFilter($tableName, $data);
         $sql="update `{$tableName}` set ";
         $tmp=array();
         $param=array();
         foreach ($data as $k=>$v){
           if(is_int($k)){   //如  hitNum=hitNum+1，可以是直接的函数
               $tmp[]=$v;
           }else if(is_numeric($v)){            // 'stateID'=>2
               $tmp[]="`{$k}`=$v";
           }else{           // 'title'=>'this is title'
               $tmp[]="`{$k}`=:k_{$k}";
               $param[":k_".$k]=$v;
             }
              
          }
          self::_paramParse($where, $whereParam);
          $param=array_merge($param,$whereParam);
          $sql.=join(",", $tmp)." where {$where}";
          return self::exec($sql, $param);
    }
    
    /**
     * 对sql语句进行预处理，同时对参数进行同步处理 ,以实现在调用时sql和参数多种占位符格式支持
     * 如 $where="id=1" , $params=1 处理成$where="id=:id",$params['id']=1
     * @param string $where
     * @param array $params
     */
    private static function _paramParse(&$where,&$params){
       if(is_null($params) || $params==""){$params=array();return;};
       
       if(!is_array($params))$params=array($params);
       $_first=each($params);
       $tmp=array();
       if(!is_int($_first['key'])){
         foreach ($params as $_k=>$_v){
              $tmp[":".ltrim($_k,":")]=$_v;
          }
       }else{
          preg_match_all("/([\w_]+)\s*\=\s*\?\s+/i", $where." ", $matches,PREG_SET_ORDER);
          if($matches){
             foreach ($matches as $_k=>$matche){
                 $where=str_replace(trim($matche[0]), $matche[1]."=:rare_".$matche[1], $where);
                 $tmp[":rare_".$matche[1]]=$params[$_k];
               }
           }
        }
       $params=$tmp;
    }
    
    /**
     * 对指定表进行删除操作
     * 如rareDb::table_delete('tableName',"id=?",array(1));
     * @param string $tableName
     * @param string $where
     * @param array $whereParam
     * @param string $dbName
     */
    public static function table_delete($tableName,$where,$whereParam=array(),$dbName=null){
         $pdo=self::getPdo($dbName); 
         self::_paramParse($where, $whereParam);
         $param=$whereParam;
         $sql="delete from `{$tableName}` where {$where}";
         return self::exec($sql, $param,$dbName);
    }
    /**
     * 记录sql日志，若firephp存在则使用firephp打印出sql语句
     * @param string $sql
     * @param array $param
     */    
    private static function _log($sql,$param){
         if(!(defined("RARE_DEBUG") && RARE_DEBUG))return;
         if(class_exists("FB",true)){
           try{
               if($param){
                    $tmp=array('sql'=>$sql,'param'=>$param);
                  }else{
                    $tmp=$sql; 
                    }
                FirePHP::addSkipFile(__FILE__);
                FB::info($tmp);    
                unset($tmp);
           }catch(Exception  $e){}
         }
        self::$sqls[]=$sql;
    }
    
    /**
     * 获取指定表的meta情况 
     * @param string $tableName
     * @param string $dbName
     */
    public static function getTableFileds($tableName,$dbName=null){
        if(!$dbName)$dbName=self::$defaultDbName;
         $cache=new rCache_object();
         $key="db_table_desc/".$dbName;
         if(!$cache->has($key)){
             $pdo=self::getPdo($dbName,'slave');
             $result=$pdo->query("show tables")->fetchAll();
             $tables=array();
             foreach ($result as $row){
                $row=each($row);
                $_desc=$pdo->query("desc `{$row['value']}`")->fetchAll();
                $tables[$row['value']]=rTookit::arrayGetCols($_desc, 'Field');
              }
              $cache->set($key, $tables);
         }else{
             $tables=$cache->get($key);
         }
        return $tables[$tableName];
    }
    
    /**
     * 根据指定的表名 对指定的数组数据进行过滤，将不表中没有定义的字段给剔除
     * @param string $tableName
     * @param array $data
     * @param string $dbName
     */
    private static function _dataFilter($tableName,&$data,$dbName=null){
         $desc=self::getTableFileds($tableName,$dbName); 
         foreach ($data as $k=>$v){
           if(!in_array($k, $desc) && !is_int($k))unset($data[$k]);
          }
    }
}