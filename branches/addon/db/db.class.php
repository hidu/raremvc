<?php
/**
 * 数据库操作模板
 * 一个简单的sql模板类，只是对pdo的简单封装,主从模式，
 * 读操作统一使用从服务器，写操作统一使用主服务器。
 * 
 *@copyright rareMVC
 *@author duwei<duv123@gmail.com>
 *@tutorial
 *<pre>
 *数据库配置文件存放在
 *1.单个应用的数据库配置: /appDir/config/db.php 
 *2.多个程序公用的数据库配置文件: /lib/config/db.php 
 *多存在两个配置文件，配置文件将会进行合并，app的配置文件将会覆盖公用配置的相同项目
 *若定义了RARE_DEBUG变量为true,并且启用了firePHP的话，可以使用firephp看到程序运行的所有的sql语句
 *并且若使用 了默认的 enableSelectCache,重复的被缓存命中的sql语句使用warn形式出现在firephp中
 *
 *一个数据库配置文件:
 &lt;?php
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

$i="user";
$db[$i]['master']['dsn']="mysql:host=127.0.0.1; port=3306; dbname=user";
$db[$i]['master']['username']="user";
$db[$i]['master']['passwd']="psw";
$db[$i]['slave']['dsn']="mysql:host=192.168.2.1; port=3306; dbname=user";
$db[$i]['slave']['username']="user";
$db[$i]['slave']['passwd']="psw";
return $db;
?&gt;
</pre>
*
*本数据库封装功能全部提供的是静态方法，是为了在使用时的方便，而且默认每个方法的最后一个参数是需要链接的数据库配置的名称，如上面的myDb,user
*如 rDB::query("select * from article where id=?", $id,"user");将指定使用配置user
*在使用过程中，也可以使用rDB::setDefaultDb('user');将默认数据库配置修改，即可以直接使用：
* rDB::query("select * from article where id=?", $id);
* 默认情况下，默认数据库配置为配置文件中的第一项数据库配置。
* 使用rDB::setDefaultDb虽然可以修改默认数据库配置，但是可能会造成代码阅读、维护困难，所以我推荐新建一个类继承rDB，如下：
* class DBUser extends rDB{
*   protected  static $defaultDbName="user";
* }
 */
class rDB{
     public static $sqls=array();
     public static $pageLabel="p";//分页参数名称
     protected  static $defaultDbName=null;//默认数据库
     protected static $enableSelectCache=true;//对 select 结果集进行缓存结果 
     
     /**
      * 设置是否启用对select语句的查询缓存
      * @param boolean $enable
      */
     public static function enableSelectCache($enable=true){
         self::$enableSelectCache=$enable;
     }
     /**
      *返回当前的select cache 状态 
      */
     public static function isCacheAble(){
       return self::$enableSelectCache;
     }
     
     /**
      * 修改当前默认的数据库链接名称,以方便后面的查询而不需要提供数据库名称
      * @param string $dbName
      */
     public static function setDefaultDb($dbName){
       self::$defaultDbName=$dbName;
     }
     
     public static function getDefaultDb(){
       return self::$defaultDbName;
     }
     
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
             if(!$config)throw new Exception("undefined dbConfig ".$type);
              $_config=array('encode'=>"utf8","username"=>null,"passwd"=>null);
             foreach ($_config as $_k=>$_v){
                 if(!isset($config[$_k]))$config[$_k]=$_v;
               }
             $dbh=new PDO($config['dsn'], $config['username'], $config['passwd'], array());
             $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, rareConfig::get('db_fetch_mode',PDO::FETCH_ASSOC));
             $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

             self::runDriver("setEncode", array($config['encode']),$dbh);
             
             $dbhs[$key]=$dbh;
         }
         return $dbhs[$key];
    }
    
    /**
     * 获取指定的数据库配置情况
     * @param string $dbName
     */
    public  static function getConfigByDbName($dbName=null){
        static $appConfigs=null;
        if($appConfigs==null){
           $dbConfig=rareConfig::getAll('db');
           $appConfigs=self::init_config($dbConfig);
           $rootDbConfigFile=rareContext::getContext()->getRootLibDir()."config/db.php";
           if(file_exists($rootDbConfigFile)){
              $shareConfig=require $rootDbConfigFile;
              if(is_array($shareConfig)){
                  $shareConfig=self::init_config($shareConfig);
                   foreach($shareConfig as $_key=>$_config){
                        if(!isset($appConfigs[$_key]))$appConfigs[$_key]=$_config;
                    }
                }
            }
        }
       if($dbName==null)$dbName=self::$defaultDbName;
       if($dbName && isset($appConfigs[$dbName])){
           return $appConfigs[$dbName];
        }else{
           return current($appConfigs);
         } 
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
     * @return PDOStatement
     */
    public static function execQuery($sql,$params=null,$dbName=null){
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
    public static function queryAll($sql,$params=null,$dbName=null){
         return self::selectWithCache($sql,$params,$dbName,true);
    }
    /**
     * 执行一条查询sql，sql使用占位符
     * @tutorial
    * <pre>
    * $id=100;
    * 1.一个参数
    * rDB::query("select * from article where id=?", $id);
    * rDB::query("select * from article where id=?", array($id));
    * rDB::query("select * from article where id=:id", array('id'=>$id));
    * 2.多个参数
    * rDB::query("select * from article where id=? and dateCreated>=?", array($id,'2012-12-1 08:12:30'));
    * rDB::query("select * from article where id=:id and dateCreated>=:date", array('id'=>$id,'date'=>'2012-12-1 08:12:30'));
    * </pre>
    * @param string $sql
    * @param array $params 当参数只有一个时也可以直接写参数而不需要写成数组
    * @param string $dbName  数据库表名
     */
    public static function query($sql,$params=null,$dbName=null){
        return self::selectWithCache($sql,$params,$dbName,false);
    }
    
    /**
     * 对select * from table_name where ... 的select 语句进行缓存查询
     * 即保证对同一个数据库相同的sql、条件只会查询数据库一次. 
     * @param string $sql
     * @param string|array $params
     * @param string $dbName
     * @param bllean $fetchAll 是否获取全部结果集
     */
    protected  static function selectWithCache($sql,$params=null,$dbName=null,$fetchAll=true){
        $cacheAble=self::$enableSelectCache;
        if(is_object($dbName)){
            $cacheAble=false;
         }
        if(is_array($params) && isset($params[':nocache']) ){
            $cacheAble=false;
            unset($params[':nocache']);
         }
        //函数调用的不进行缓存 比如  SELECT FOUND_ROWS()
        if( $cacheAble && preg_match("/\(\s*\)/", $sql) ){
            $cacheAble=false;
         }
        if($cacheAble){
            static $cache=array();
            $key=$sql.serialize($params).$dbName;
            $key=md5($key);
            if(array_key_exists($key, $cache)){
                self::_log($sql, $params,"warn");
                return $cache[$key];
             }
        }
       $sth=self::execQuery($sql, $params,$dbName);
       $result=$fetchAll?$sth->fetchAll():$sth->fetch();
        if($cacheAble){
           if(count($result)>100)return $result;//结果集比较大时也不缓存
           if(count($cache)>1000)array_shift($cache);//最多缓存1000条结果
           $cache[$key]=$result;
        }
       return $result;
    }
    
    /**
     * 分页查询
     *@tutorial
     *<pre> 
     * list($list,$pager)=rareDb::listPage("select * from artilce where cateID=? and createTime>?",array(1,date('Y-m-d H:i:s')),20);
     * $list是一个数组，为我们查询的数据
     * $pager  为一个 rarePager 对象，以实现__toString方向，可以在模板中直接输出 
     *   如&lt;?php echo $pager->setlinkNum(5);//每页显示5个链接，并输出?&gt;
     *  当前只实现了mysql 分页查询，其他数据库可以使用hook功能来实现
     *  &lt;?php
     *  class myRareDb{
     *      public static function mssql($sql,$params=array(),$size=10,$dbName=null){
     *          //@todo
     *          return array($resultList,$totleNum);
     *        }
     *  }?&gt;
     *  </pre>   
     * @param string $sql
     * @param array $params
     * @param int $size
     * @param string $dbName
     * @return array     ($list,$pager) 
     */
    public static function listPage($sql,$params=null,$size=10,$dbName=null){
        $page=isset($_GET[self::$pageLabel])?(int)$_GET[self::$pageLabel]:1;
        $page=$page>0? $page:1;
        $sql=trim($sql);
        $result=self::runDriver('listPage',array($sql,$params,$size,$page),self::getPdo($dbName));
        if($result===false){
             throw new Exception('no driver for'.$driver_name);
          }
        list($list,$total)=$result;
        $pageInfo=array();
        $pageInfo['page']=$page;
        $pageInfo['size']=$size;
        $pageInfo['total']=$total;

         //尝试使用自定义的分页类，该类需要和 rPager 有同样的api,该类名是 custom_rPager，可以是继承自 rPager
         //若没有自定义的pager类则使用默认的 rPager类
        $pagerClass="custom_rPager";
        if(!class_exists($pagerClass,true)){
          $pagerClass="rPager";
         }
        
        return array($list,new $pagerClass($pageInfo));
     }
     
     protected  static function runDriver($fn,$params,$pdo){
         $driver_name=strtolower($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
         $class='rdb_driver_'.strtolower($driver_name);
         if(class_exists($class,true) && method_exists($class, $fn)){
            $params[]=$pdo;
            return call_user_func_array(array($class,$fn), $params);
          }else{
           throw new Exception('no driver for '.$driverName.":\t".$fn);
          }
        return false;
     }
     
    /**
     * 
     * @param string $sql
     * @param array $params
     * @param string $dbName
     * @return int
     */
    public static function exec($sql,$params=null,$dbName=null){
        $pdo=self::getPdo($dbName,'master');
        self::_paramParse($sql, $params);
        $sth=self::_preExec($pdo, $sql, $params);
        return $sth->rowCount();
    }
    /**
     * @param PDO $pdo
     * @param string $sql
     * @param array $params
     * @throws Exception
     * @return PDOStatement
     */
    private static function _preExec($pdo,$sql,$params){
       try{
          $sth=$pdo->prepare($sql);
          $sth->execute($params);
          self::_log($sql, $params);
        }catch (Exception $e){
            throw new Exception($e->getMessage()."\nsql:\n".$sql."\ndata:\n".print_r($params,true)."\n");
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
         if(empty($data))return false;
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
    public static function table_update($tableName,$data,$where,$whereParam=null,$dbName=null){
         if(is_string($data))$data=array($data);
         self::_dataFilter($tableName, $data);
         $sql="update `{$tableName}` set ";
         $tmp=array();
         $param=array();
         foreach ($data as $k=>$v){
           if(is_int($k)){   //如  hitNum=hitNum+1，可以是直接的函数
               $tmp[]=$v;
           }else{           //其他情况全部使用占位符 'title'=>'this is title'
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
    public static function _paramParse(&$where,&$params){
       if(is_null($params)){$params=array();return;};
       
       if(!is_array($params))$params=array($params);
       $_first=each($params);
       $tmp=array();
       if(!is_int($_first['key'])){
         foreach ($params as $_k=>$_v){
              $tmp[":".ltrim($_k,":")]=$_v;
          }
       }else{
          preg_match_all("/`?([\w_]+)`?\s*[\=<>!]+\s*\?\s+/i", $where." ", $matches,PREG_SET_ORDER);
          if($matches){
             foreach ($matches as $_k=>$matche){
                 $fieldName=":".$matche[1];//字段名称
                 $i=0;
                 while (array_key_exists($fieldName, $params)){
                      $fieldName=":".$matche[1]."_".($i++);
                    }
                 $where=str_replace(trim($matche[0]), str_replace("?", $fieldName, $matche[0]), $where);
                 if(array_key_exists($_k, $params)){
                   $tmp[$fieldName]=$params[$_k];
                   }
               }
           }
        }
       $params=$tmp;
        
        //------------------------------------------
        //fix sql like: select * from table_name where id in(:ids)
        preg_match_all("/\s+in\s*\(\s*(\:\w+)\s*\)/i", $where." ", $matches,PREG_SET_ORDER);
        if($matches){
            foreach ($matches as $_k=>$matche){
                $fieldName=trim($matche[1],":");
                $_val=$params[$matche[1]];
                if(!is_array($_val)){
                    $_val=explode(",", addslashes($_val));
                  }
                $_tmpStrArray=array();
                foreach ($_val as $_item){
                    $_tmpStrArray[]=is_numeric($_item)?$_item:"'".$_item."'";
                  }
                $_val=implode(",", $_tmpStrArray);
                $where=str_replace($matche[0], " In (".$_val.") ", $where);
                unset($params[$matche[1]]);
              }
         }
        //==========================================
    }
    
    /**
     * 对指定表进行删除操作
     * 如rareDb::table_delete('tableName',"id=?",array(1));
     * @param string $tableName
     * @param string $where
     * @param array $whereParam
     * @param string $dbName
     */
    public static function table_delete($tableName,$where,$whereParam=null,$dbName=null){
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
    private static function _log($sql,$param,$fbType="info"){
         if(!(defined("RARE_DEBUG") && RARE_DEBUG))return;
         if(class_exists("FB",true)){
           try{
                if($param){
                   $tmp=array('sql'=>$sql,'param'=>$param);
                 }else{
                   $tmp=$sql;
                    }  
                FirePHP::addSkipFile(__FILE__);
                FirePHP::addSkipFile(dirname(__FILE__)."/driver/mysql.class.php");
                FirePHP::addSkipFile(dirname(__FILE__)."/driver/postgresql.class.php");
                FirePHP::addSkipFile(dirname(__FILE__)."/driver/sqlite.class.php");
                if($fbType=="info"){
                  FB::info($tmp);    
                 }else{
                   FB::warn($tmp);
                  }
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
             $result=self::getAllTables();
             $tables=array();
             foreach ($result as $tabName){
                $_desc=self::getTableDesc($tabName);
                $tables[$tabName]=qArray::getCols($_desc, 'name');
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
    /**
     * 获取所有的表
     * @param string $dbName
     * @param string $type
     * @return array
     */
    public static function getAllTables($dbName=null,$type='slave'){
        return self::runDriver('getAllTables',null,self::getPdo($dbName,$type));
    }
    
    /**
     * 获取表所有的字段以及类型
     * @param string $tableName
     * @param string $dbName
     * @param string $type
     * @return array
     */
    public static function getTableDesc($tableName,$dbName=null,$type='slave'){
       return self::runDriver('getTableDesc',array($tableName),self::getPdo($dbName,$type));
    }
}
