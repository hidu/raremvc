<?php
/**
 * 一个超级强大的数组的工具类
 * @author duwei<duv123@gmail.com>
 *
 */
class rArray{
    
 /**
  * 剔除空数组元素
  * @param array $arr
  * @param string $trim 是否进行trim
  */
   public static function removeEmpty(&$arr,$trim=true){
       if(!is_array($arr))return;
       foreach ($arr as $k=>$v){
         if(is_array($v)){
            self::removeEmpty($v,$trim);
         }else if(is_string($v)){
           $v=trim($v);
           if($v==""){
                unset($arr[$k]);  
            }else{
                $arr[$k]=$v;
               }
             }    
         }
   }
   /**
    * 获取一个二维数组的指定的列
    * @example
    * <pre>
    * $arr=array(
    *    array('id'=>1,'name'=>"aaa"),
    *    array('id'=>2,'name'=>"bbb"),
    *    array('id'=>3,'name'=>"ccc"),
    *    )
    * getCols($arr,'id');
    * 得到结果：
    * array(1,2,3)
    * </pre>
    * @param array $arr
    * @param String $colName
    * @param boolean $unique 结果是否去重
    * @return array
    */
   public static function getCols($arr,$colName,$unique=false){
      if(!is_array($arr))return array();
      $result=array();
      foreach ($arr as $one){
          if(isset($one[$colName])){
           $result[]=$one[$colName];
           }
      }
      if($unique)$result=self::unique($result);
      return $result;
   }
   
   public static function unique($arr){
       if(!is_array($arr))return array();
       return array_map("unserialize", array_unique(array_map("serialize", $arr)));
   }
   
   /**
    * 获取二维数组的一个key的统计数据
    * @param array $arr
    * @param string $colName
    * @param string $type 类型：max、min、sum、avg
    * @return NULL|mixed|number
    */
   public static function getCol($arr,$colName,$type){
    if(!is_array($arr)||empty($arr))return null;
    $type=strtolower($type);
    $colsVals=self::getCols($arr, $colName);
    if("max" == $type){
        return max($colsVals);
    }else if('min'==$type){
        return min($colsVals);
    }else if('sum' ==$type){
        return array_sum($colsVals);
     }else if('avg'==$type){
         $c=count($colsVals);
        return $c==0?0:array_sum($colsVals)/$c;
     }
     return null;
   }
   
   /**
    * @example
    * <pre>
    *       $arr=array(
                array('id'=>1,'name'=>"aaa"),
                array('id'=>array('a','b'),'name'=>"ccc"),
        );
        $result=rArray::getRow($arr,"1.id.0");
        得到结果为'a'
    * </pre>
    * 获取多维数组的一行数据
    * @param array $arr
    * @param string $rowName 如“0.name”
    * @param string $type 结果类型 所有的settype方法支持的类型
    * @return Ambigous <unknown, NULL>
    */
   public static function getRow($arr,$rowName,$type=null){
       $row_arr=explode(".", preg_replace("/\s/", "", $rowName));
       $data=$arr;
       foreach ($row_arr as $name){
           $data=is_array($data) && isset($data[$name])?$data[$name]:null;
       }
       if($type)settype($data, $type);
       return $data;
   }
   
   /**
    * 将一个二维数组 按照指定的$k作为主键
    * 若$vk不为空则其值为$vk的值
    * 如
    * @example
    * <pre>
    * $arr=array(
    *    array('id'=>1,'name'="aaa"),
    *    array('id'=>2,'name'="bbb"),
    *    array('id'=>3,'name'="ccc"),
    *    )
    * toHash($arr,'id');
    *  结果为：
    *  $arr=array(
    *    1=>array('id'=>1,'name'="aaa"),
    *    2=>array('id'=>2,'name'="bbb"),
    *    3=>array('id'=>3,'name'="ccc"),
    *    )
    *  toHash($arr,'id','name');
    *    结果为：
    *  $arr=array(
    *    1=>"aaa",
    *    2=>"bbb",
    *    3=>"ccc",
     *    )
    *  </pre>
    * @param array $arr
    * @param string $k key字段
    * @param string $vk 可选的值的字段，若不为空则值为该字段的值
    * @return array
    */
   public static function toHash($arr,$k,$vk=null){
    if(!is_array($arr))return array();
       $result=array();
       foreach ($arr as $one){
        if(isset($one[$k])){
            $value=empty($vk)?$one:(isset($one[$vk])?trim($one[$vk]):"");
            $result[trim($one[$k])]=$value;
           } 
         }
      return $result;
   }
   /**
    * 将hashMap转换为普通数组
    * @example
    * <pre>
    * $arr=array(
    *    1=>'a',
    *    2=>'b',
    * );
    * self::hashToArray($arr,'id','name');
    * 结果为：
    * array(
    *    array('id'=>1,'name'=>'a'),
    *    array('id'=>2,'name'=>'b'),
    * );
    * </pre>
    * @param array $arr
    * @param string $keyName
    * @param string $valName
    * @return array
    */
   public static function hashToArray($arr,$keyName,$valName){
    if(!is_array($arr))return array();
    $result=array();
    foreach($arr as $k=>$v){
       $result[]=array($keyName=>$k,$valName=>$v);
      }
     return $result;
   }
   
   /**
    * 将二维数组按照指定的键分组
    *   * 如
    * @example
    * <pre>
    * $arr=array(
    *    array('id'=>1,'name'="aaa"),
    *    array('id'=>1,'name'="bbb"),
    *    array('id'=>3,'name'="ccc"),
    *    )
    * toHash($arr,'id');
    *  结果为：
    *  $arr=array(
    *    1=>array(
    *      array('id'=>1,'name'="aaa"),
    *      array('id'=>1,'name'="bbb")
    *          ),
    *    3=>array(
    *       array('id'=>3,'name'="ccc"),
    *          )
    *    )
    *  toGroup($arr,'id');
    *    结果为：
    *  $arr=array(
    *    1=>"aaa",
    *    2=>"bbb",
    *    3=>"ccc",
     *    )
    *  </pre>
    * @param array $arr
    * @param string $key
    * @return array
    */
   public static function toGroup($arr,$key){
    if(!is_array($arr))return array();
     $result=array();
     foreach ($arr as $one){
        if(isset($one[$key])){
            $result[$one[$key].""][]=$one;
          }
       }
     return $result;
   }
   
   /**
    * 像使用sql的order by 一样 对一个多维数组进行排序
    * @example
    * <pre>
    * $arr=array(
  "a"=>array('a'=>1,'b'=>"ad",'c'=>array('d'=>'9')),
  "b"=>array('a'=>2,'b'=>"cd",'c'=>array('d'=>'12')),
  'c'=>array('a'=>2,'b'=>"dd",'c'=>array('d'=>'1')),
  'e'=>array('a'=>20,'b'=>"aa"),
);
self::orderBy($arr, "b desc");
结果为：
Array(
  'c'=>array('a'=>2,'b'=>"dd",'c'=>array('d'=>'1')),
  "b"=>array('a'=>2,'b'=>"cd",'c'=>array('d'=>'12')),
  "a"=>array('a'=>1,'b'=>"ad",'c'=>array('d'=>'9')),
  'e'=>array('a'=>20,'b'=>"aa"),
)
    * </pre>
    * @param array $arr 待排序的数组
    * @param 排序条件 $cond 如 <font color=red>updateTime desc,uid asc,more.updateTime desc</font>
    */
   public static function orderBy(&$arr,$cond){
    if(!is_array($arr))return false;
    $cond_arr=explode(",", $cond);
    $code="";
    foreach ($cond_arr as $_con){
      $_sub_con_arr=preg_split("/\s+/", trim($_con));
      $_k_name=$_sub_con_arr[0];
      $_sort_type=isset($_sub_con_arr[1])?strtolower($_sub_con_arr[1]):'asc';
      $_k_name=str_replace(".", "']['", $_k_name);
      $a="\$a['".$_k_name."']";
      $b="\$b['".$_k_name."']";
      $c=$_sort_type=="desc"?"<":">";
      $code.='if('.$a.'!='.$b.')return '.$a.$c.$b.";\n";
    }
    $code.="return true;";
    return @uasort($arr, create_function('$a,$b', $code));
   }
   
   /**
    * 对多维数组按照条件进行筛选
    * @example
    * <pre>$arr=array(
            array('id'=>1,'name'=>"aaa"),
            array('id'=>2,'name'=>"bbb"),
            array('id'=>3,'name'=>"ccc"),
            array('id'=>'4','name'=>"ccc"),
            array('id'=>array('a','b'),'name'=>"ccc"),
            array('id'=>array('a','b'),'name'=>"ddd"),
    );
    <font color=blue>$cond="(id>=1 and id<2) and name=aaa or id.0=a or id==4";</font>
       $result= rArray::filter($arr, $cond);
       </pre>
    * @param array $arr
    * @param string $cond 筛选条件,支持>=<、in、not in筛选 如 <font color=red>(id>=1 and id<2) and name=aaa or id.0=a or id==4 or id in (1)</font>
    * @return array
    */
   public static function filter($arr,$cond){
      $cond=" ".preg_replace("/[\(\)]/"," \\0 ", $cond)." ";
      $cond_str= preg_replace_callback("/\s(\S+?)\s*([>=<]={0,2})\s*[\"']?(.+?)[\"']?\s/", array('self','_filter_callback_1'), $cond);
      $cond_str= preg_replace_callback("/\s(\S+?)\s*((\snot\s+)?in)\s*\((.+?)\)\s/", array('self','_filter_callback_2'), $cond_str);
// print_r($cond_str);die;
      $function=create_function('$a', "return (".$cond_str.");");
      $result=array_filter($arr,$function);
     return $result;
   }
   /**
    * 处理比较操作
    * @param array $matches
    * @return string
    */
   private static function _filter_callback_1($matches){
//        print_r($matches);
       $name='$a["'.str_replace(".", '"]["', $matches[1]).'"]';
       $s=$matches[2]=="="?"==":$matches[2];
       $val=is_numeric($matches[3])?$matches[3]:'"'.$matches[3].'"';
       return " \n(isset(".$name.") && ".$name.$s.$val.") \n ";
   }
   /**
    * 处理in,not in操作
    * @param array $matches
    * @return string
    */
   private static function _filter_callback_2($matches){
//        print_r($matches);
       $name='$a["'.str_replace(".", '"]["', $matches[1]).'"]';
       $type=trim(preg_replace("/\s+/"," ",$matches[2]));
       $s=$type=="not in"?"!in_array(":"in_array(";
       $vs=explode(",", $matches[4]);
       foreach ($vs as &$v){
           $v=preg_replace("/^[\"']|[\"']$/", "", trim($v));
       }
       $vs_str=preg_replace("/([\n\r]\s+\d+\s*=>\s*)|[\n]/","",var_export($vs,true));
//        print_r($vs_str);
       return " \n(isset(".$name.") && ".$s.$name.",".$vs_str.")) \n ";
   }
   
   /**
    * 将二维数组转换为属性结构
    * @example
    * <pre>
*         $arr=array(
            array('id'=>1,'pid'=>0),
            array('id'=>2,'pid'=>0),
            array('id'=>3,'pid'=>1),
            array('id'=>4,'pid'=>3),
            array('id'=>5,'pid'=>1),
            );
     $result= self::toTree($arr, "id", 'pid','children');
     结果为:
     array (0 =>array (
                    'id' => 1,
                    'pid' => 0,
                    'children' => 
                            array (
                              0 => 
                              array (
                                'id' => 3,
                                'pid' => 1,
                                'children' =>  array (
                                     0 => array ('id' => 4,'pid' => 3, 'children' => array (),),
                                ),
                              ),
                              1 =>  array ('id' => 5,'pid' => 1, 'children' => array (),),
                            ),
                  ),
              1 => array ( 'id' => 2,'pid' => 0,'children' => array (),
              ))
    * </pre>
    * @param array $arr
    * @param string $idField  id字段名称
    * @param string $parentIdField 父id字段名称
    * @param string $childField 子节点名称
    * @return array
    */
   public static function toTree($arr,$idField,$parentIdField,$childField='children'){
       if(!is_array($arr))return array();
       $map_index=self::toHash($arr, $idField,$parentIdField);
       $result=array();
       $index=0;
       $childrens=array();
       foreach ($map_index as $id=>$parentid){
           if(!isset($map_index[$parentid])){
              $result[]=$arr[$index]; 
           }else{
              $childrens[$id]=$arr[$index];
           }
           $index++;
       }
       foreach ($result as $i=>$row){
           $row[$childField]=self::_getAllChildren($childrens, $idField, $parentIdField,$childField,$row[$idField]);
           $result[$i]=$row;
       }
       return $result;
   }
   
   private static function _getAllChildren(&$arr,$idField,$parentIdField,$childField,$idValue){
       $map=self::toGroup($arr, $parentIdField);
       if(isset($map[$idValue])){
           $cs=$map[$idValue];
           foreach($cs as $i=>$v){
               unset($arr[$v[$idField]]);
           }
           foreach ($cs as &$sub){
               $sub[$childField]=self::_getAllChildren($arr, $idField, $parentIdField, $childField, $sub[$idField]);
           }
           return $cs;
       }else{
           return array();
       }
   }
   
}