<?php
require_once 'PHPUnit/Autoload.php';
require dirname(dirname(__FILE__)).'/other/array.class.php';
class array_test extends PHPUnit_Framework_TestCase{
   private $arr=array(
            array('id'=>1,'name'=>"aaa",'i'=>1),
            array('id'=>2,'name'=>"bbb",'i'=>2),
            array('id'=>3,'name'=>"ccc",'i'=>3),
            array('id'=>'4','name'=>"ccc",'i'=>4),
            array('id'=>array('a','b'),'name'=>"ccc",'i'=>5),
            array('id'=>array('a','b'),'name'=>"ddd",'i'=>6),
            array('id'=>array('a','b','c'),'name'=>"eee",'i'=>7,'m'=>array('f'=>'eee'),"名字"=>"rArray数组"),
    );
    
    public function test_getCols(){
        $res=rArray::getCols($this->arr, "id",true);
        $result=array(1,2,3,4,array("a","b"),array("a","b","c"));
        $res=array_values($res);
//         print_r($res);
        $this->assertEquals($result, $res,"test_getCols failed");
    }
    
    public function test_getRow(){
        $outMap=array("3.i"=>4,"4.id.0"=>'a','100'=>null);
        foreach ($outMap as $k=>$v){
           $res=rArray::getRow($this->arr, $k);
           $this->assertEquals($v, $res);
        }
        $res=rArray::getRow($this->arr, "100",'array');
        $this->assertEquals(array(), $res);
        $res=rArray::getRow($this->arr, "100",'int');
        $this->assertEquals(0, $res);
    }
    
    public function test_filter(){
       $result= rArray::filter($this->arr, "(id>=1 and id<2) and name=aaa or id.2=c or id=='4'");
       $a1=array(0,3,6);
      $this->assertEquals($a1, array_keys($result));
      $result= rArray::filter($this->arr, "名字=rArray数组");
      $this->assertEquals(array(6), array_keys($result));
      
      $result=rArray::filter($this->arr, " id in ('1',2)");
      $this->assertEquals(2, count($result));
      $this->assertEquals(1, $result[0]['id']);
      $this->assertEquals(2, $result[1]['id']);
      
      
      $result=rArray::filter($this->arr, " id not in ('1',2, 3) and name not in(ddd,eee)");
//       print_r($result);
      $this->assertEquals(2, count($result));
      $this->assertEquals(4, $result[3]['id']);
      $this->assertEquals(5, $result[4]['i']);
      
      $result=rArray::filter($this->arr, "名字 in( rArray数组)");
      $this->assertEquals($this->arr[6], $result[6]);
      $this->assertEquals(1, count($result));
      
      $result=rArray::filter($this->arr, "名字 in ( \"rArray数组\")");
      $this->assertEquals($this->arr[6], $result[6]);
      $this->assertEquals(1, count($result));
      
      $result=rArray::filter($this->arr, "名字 in( 'rArray数组')");
      $this->assertEquals($this->arr[6], $result[6]);
      $this->assertEquals(1, count($result));
      
      $result=rArray::filter($this->arr, "id!=1 and id<3");
      $this->assertEquals($this->arr[1], $result[1]);
      $this->assertEquals(1, count($result));

      $result= rArray::filter($this->arr, "(id>=1 and id<2) and name=aaa  or substr(name,1,1)=d");
     $this->assertEquals(6,$result[5]['i']);
     $this->assertEquals(1,$result[0]['i']);
     $this->assertEquals(2,count($result));
//       try{
//         $result=rArray::filter($this->arr, "名字 in( 'rArray数组') hello");
//       }catch(Exception $e){}
//       $this->assertFalse($result);
//      preg_match_all("/(\S+)\s?=\s?(\S+)\s/", " 名字1.1 = rArray asdasd",$matches);
//      preg_match_all("/\s(\S+?)\s*([>=<]={0,2})\s*[\"']?(.+?)[\"']?\s/", " 名字1.1 = rArray asdasd",$matches);
//      print_r($matches);

    }
    
    public function test_toTree(){
        $arr=array(
                array('id'=>1,'pid'=>0),
                array('id'=>2,'pid'=>0),
                array('id'=>3,'pid'=>1),
                array('id'=>4,'pid'=>3),
                array('id'=>5,'pid'=>1),
                );
       $result= rArray::toTree($arr, "id", 'pid','children');
//        var_export($result);
       $this->assertEquals(2, count($result));
       $this->assertEquals(4, $result[0]['children'][0]['children'][0]['id']);
       $this->assertEquals(2, $result[1]['id']);
    }
}
// $a=new array_test();
// $a->test_filter();