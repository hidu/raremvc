<?php
/**
 * crontab cycle parse
 * @author duwei
 */
class rCycle{
    
    /**
     * 检测一个规则是否符合当前时间/指定时间
     * @param string $cycle
     * @param int $time
     */
    public static function isCurrect($cycle,$time=null){
        $cycle_arr=preg_split("/\s+/", trim($cycle));
        if(empty($cycle_arr) || count($cycle_arr)!=5)return false;
        $date_info=explode("-", date("i-H-j-m-N",$time?$time:time()));
        $result=true;
        foreach($cycle_arr as $i=>$t){
            $result=$result && self::isItemCurrect($t, $date_info[$i]);
        }
        return $result;
    }
    
    private static  function isItemCurrect($str,$val){
        if($str=="*" || $str==$val)return true;
        if(preg_match("/^\*\/(\d+)$/", $str,$matches)){
            return $val%$matches[1]==0;
        }
        if(preg_match("/(\d+,)+\d+/",$str,$matches)){
            $tmps=explode(",", $str);
            return in_array($val, $tmps);
        }
        return false;
    }
    
    /**
     * 验证格式
     * @param string $cycle
     * @throws Exception
     */
    public static function validate($cycle){
        $cycle_arr=preg_split("/\s+/", trim($cycle));
        if(!$cycle_arr || count($cycle_arr)!=5)throw new Exception("应该是5项！当前是：".$cycle);
        if(!self::checkSingle($cycle_arr[0], 60)){
            throw new Exception("分钟配置不对，当前是：".$cycle_arr[0]);
        }
        if(!self::checkSingle($cycle_arr[1], 24)){
            throw new Exception("小时配置不对,当前是:".$cycle_arr[1]);
        }
        if(!self::checkSingle($cycle_arr[2], 32,1)){
            throw new Exception("日配置不对,当前是:".$cycle_arr[2]);
        }
        if(!self::checkSingle($cycle_arr[3], 13,1)){
            throw new Exception("月配置不对,当前是:".$cycle_arr[3]);
        }
        if(!self::checkSingle($cycle_arr[4], 8,1)){
            throw new Exception("星期配置不对,当前是:".$cycle_arr[4]);
        }
        return true;
    }
    
    private static function checkSingle($str,$max,$min=0){
        if($str=="*" || self::isIntAndLt($str, $max,$min))return true;
        $tmp=explode("/", $str);
        if($tmp[0]=="*" && self::isIntAndLt($tmp[1], $max,$min))return true;
        $tmp=explode(",", $str);
        $rt=true;
        foreach ($tmp as $sub){
            if(!self::isIntAndLt($sub, $max,$min))$rt=false;
        }
        if($rt)return true;
    
        return false;
    }
    
    private  static function isIntAndLt($str,$max,$min){
        return preg_match("/^\d+$/", $str) && $str>=$min && $str<$max;
    }
}