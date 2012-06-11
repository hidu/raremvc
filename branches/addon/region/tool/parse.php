<?php
/**
 * http://www.stats.gov.cn/tjbz/xzqhdm/
 * 解析 行政区划代码
 */
$list=file(dirname(__FILE__).'/full_list');

$outDir=dirname(__FILE__)."/out/";
if(!file_exists($outDir)){
    mkdir($outDir);
}
function export_file($file,$arr){
    $str="<?php\n return ".var_export($arr,true).";";
    file_put_contents(dirname(__FILE__)."/out/".$file, $str);
}
$zhixiashi=array("110000","120000","310000","500000");

$prov=array();
foreach ($list as $row){
    $line=preg_split("/\s+/", trim($row));
    if(preg_match("/\d{2}0{4}/", $line[0])){
        $prov[$line[0]]=preg_replace("/(省|市)$/","",$line[1]);
    }
}
export_file("prov.php",$prov);
//print_r($prov);

$cities=array();
foreach ($list as $row){
    $line=preg_split("/\s+/", trim($row));
    if(substr($line[0], 2,2)!="00" && preg_match("/\d{4}0{2}/", $line[0])){
        $pid=substr($line[0],0,2)."0000";
        if(in_array($pid, $zhixiashi)){
            if(isset($cities[$pid]) && count($cities[$pid])==1)continue;
            $cities[$pid][$line[0]]=$prov[$pid];
         }else{
            $cities[$pid][$line[0]]=preg_replace("/市$/","",$line[1]);
         }
    }
}
export_file("city.php",$cities);

$counties=array();

$last_countyId=0;
$i=0;
foreach ($list as $row){
    $line=preg_split("/\s+/", trim($row));
    if(substr($line[0], 4,2)!="00" && $line[1]!="市辖区"){
        $pid=substr($line[0],0,2)."0000";
        $cur_countyId=$line[0];
        if(in_array($pid, $zhixiashi)){
            $cid=substr($line[0],0,2)."0100";
            if($last_countyId && substr($last_countyId, 0,4)!=substr($cur_countyId, 0,4)){
              $cur_countyId=(substr($line[0],0,2)."0150")+$i++;
            }
        }else{
            $cid=substr($line[0],0,4)."00";
            $i=0;
        }
         $last_countyId=$cur_countyId;
        $counties[$cid][$cur_countyId]=mb_strlen($line[1])>6?preg_replace("/(县|市)$/","",$line[1]):$line[1];
    }
}
export_file("county.php",$counties);

$location=array('prov'=>$prov,'city'=>$cities,'county'=>$counties);

function _tmp_encode($arr){
    foreach ($arr as $k=>$v){
        if(is_array($v)){
            $arr[$k]=_tmp_encode($v);
        }else{
            $arr[$k]=urlencode($v);
        }
    }
    return $arr;
}
$region=_tmp_encode($location);
$region_str=urldecode(json_encode($region));
file_put_contents(dirname(__FILE__)."/out/region.js", "var region=".$region_str.";");

