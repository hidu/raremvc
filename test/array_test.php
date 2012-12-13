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

   public function test_nameSplit(){
       $res=rArray::nameSplit("a.b.c");
       $this->assertEquals(array("a","b","c"), $res);
       $res=rArray::nameSplit("杜.伟");
       $this->assertEquals(array("杜","伟"), $res);
       $res=rArray::nameSplit("杜\.伟");
       $this->assertEquals(array("杜.伟"), $res);
       $res=rArray::nameSplit("a.b.c\.d");
       $this->assertEquals(array("a","b","c.d"), $res);
   }
   
    public function test_getCols(){
        $res=rArray::getCols($this->arr, "id",true);
        $result=array(1,2,3,4,array("a","b"),array("a","b","c"));
        $res=array_values($res);
//         print_r($res);
        $this->assertEquals($result, $res,"test_getCols failed");
        
        $res=rArray::getCols($this->arr, "name");
        $result=array();
        foreach ($this->arr as $_tmp){
            $result[]=$_tmp['name'];
        }
        $this->assertEquals($result, $res);
        
        $res=rArray::getCols($this->arr, "m.f");
        $this->assertEquals(array("eee"), $res);
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
    
    public function test_filter_match(){
        $result=rArray::filter($this->arr, "match(name,'a*') and id<2");
        $this->assertEquals(array($this->arr[0]), $result);
        
        $result=rArray::filter($this->arr, "match(name,/a.*/) and id<2");
        $this->assertEquals(array($this->arr[0]), $result);
        
        $result=rArray::filter($this->arr, "match(name,/a(.*)/) and id<2");
        $this->assertEquals(array($this->arr[0]), $result);
        
        $result=rArray::filter($this->arr, "!match(name,/a(.*)/) and id<3");
        $this->assertEquals(array(1=>$this->arr[1]), $result);
    }
    
    public function _test_filter(){
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
    
    public function test_groupby(){
        $arr=array( array('id'=>1,'name'=>"aaa",'c'=>array('a'=>'a',2)),
                    array('id'=>1,'name'=>"bbb"),
                    array('id'=>3,'name'=>"ccc",'c'=>array('a'=>'a',2)),
                    );
        $res=rArray::groupBy($arr, "id");
        $this->assertEquals(2, count($res));
        $this->assertEquals(2, count($res[1]));
        $this->assertEquals($arr[0], $res[1][0]);
        $this->assertEquals($arr[1], $res[1][1]);
        $this->assertEquals($arr[2], $res[3][0]);
        
        $res=rArray::groupBy($arr, "c.a");
        $this->assertEquals(array('a'=>array($arr[0],$arr[2]),""=>array($arr[1])), $res);
    }
    
    public function test_select(){
        $res=rArray::select($this->arr, "name as 名字");
        $result=array();
        foreach ($this->arr as $tmp){
            $result[]=array("名字"=>$tmp["name"]);
        }
        $this->assertEquals($result, $res);
        $res=rArray::select($this->arr, "id.0 as id0,n*me,i");
        $result=array (0 =>array ('id0' => NULL,'name' => 'aaa','i' => 1,),1 =>array ('id0' => NULL,'name' => 'bbb','i' => 2,),2 =>array ('id0' => NULL,'name' => 'ccc','i' => 3,),3 =>array ('id0' => NULL,'name' => 'ccc','i' => 4,),4 =>array ('id0' => 'a','name' => 'ccc','i' => 5,),5 =>array ('id0' => 'a','name' => 'ddd','i' => 6,),6 =>array ('id0' => 'a','name' => 'eee','i' => 7,),);
        $this->assertEquals($result, $res);
        
        $res=rArray::select($this->arr, "na\we/e");
        $res1=rArray::select($this->arr, "name");
        $this->assertEquals($res, $res1);
        
        $arr=array(
                array("aaaa"=>'1'),
                array("aaa"=>'2'),
                array("aa"=>'3'),
                );
        $res=rArray::select($arr, "a{2\,3}/e");
        $result=array (0 =>array (),1 =>array ('aaa' => '2',),2 =>array ('aa' => '3',),);
        $this->assertEquals($result, $res);
    }
    
    public function test_BySql(){
          $res=rArray::selectByFullSql($this->arr, "select id,name as 名字 where id >1 order by id desc group by id");
          $result=array (4 =>array (0 =>array ('id' => '4','名字' => 'ccc',),),3 =>array (0 =>array ('id' => 3,'名字' => 'ccc',),),2 =>array (0 =>array ('id' => 2,'名字' => 'bbb',),),);
           $this->assertEquals($result, $res);
           
//           $this->_export($result);
        //         rArray::bySql($this->arr, "select id,name as 名字 ");
    }
    
    private function _export($res){
        print_r("\n");
        print_r($res);
        $str=var_export($res,true);
        echo "\n".preg_replace("/\s*\n\s*/", "", $str)."\n";
    }
    
    public function test_mergeDeep(){
        
        $a=array('a'=>array('b'=>'c'),'d'=>'d','f'=>array('f1'),'e'=>array('e1'=>array('ee1'=>'ea')));
        $b=array('a'=>array('c'=>'d'),'d'=>'','f'=>array(),'e'=>array('e1'=>array('ee1'=>'ea1')));
        $res=rArray::mergeDeep($a,$b);
        $result1=array ('a' =>array ('b' => 'c','c' => 'd',),'d' => 'd','f' =>array (0 => 'f1',),'e' =>array ('e1' =>array ('ee1' => 'ea1',),),);
        $this->assertEquals($result1, $res);

        $res=rArray::mergeDeep($a,$b,1);
        $this->assertEquals($result1, $res);

        $res=rArray::mergeDeep($a,$b,true);
        $result1=array ('a' =>array ('b' => 'c','c' => 'd',),'d' => '','f' =>array (),'e' =>array ('e1' =>array ('ee1' => 'ea1',),),);
        $this->assertEquals($result1, $res);
        
        $res=rArray::mergeDeep();
        $this->assertFalse($res);
        
        $res=rArray::mergeDeep($a);
        $this->assertEquals($a, $res);
        
        $res=rArray::mergeDeep($a,1);
        $this->assertEquals($a, $res);
    }
    
}
// $a=new array_test();
// $a->test_getCols();