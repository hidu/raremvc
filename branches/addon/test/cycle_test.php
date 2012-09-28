<?php
require_once 'PHPUnit/Autoload.php';
require dirname(dirname(__FILE__)).'/other/cycle.class.php';
class cycle_test extends PHPUnit_Framework_TestCase{
    
    public function testValidate(){
        $this->assertTrue(rCycle::validate("* * * * *"));
        $this->assertTrue(rCycle::validate("1 * * * *"));
        $this->assertTrue(rCycle::validate("2 * * * *"));
        $this->assertTrue(rCycle::validate("59 * * * *"));
        $this->assertTrue(rCycle::validate("59 0 * * *"));
        $this->assertTrue(rCycle::validate("59 23 * * *"));
        $this->assertTrue(rCycle::validate("59 */2 * * *"));
        $this->assertTrue(rCycle::validate("1,2,59 */2 * * *"));
        $this->assertTrue(rCycle::validate("*/5 */2 * * *"));
        $this->assertTrue(rCycle::validate("*/5 */2 * * 1,2"));
        $this->assertTrue(rCycle::validate("*/5 */2 1 * *"));
        $this->assertTrue(rCycle::validate("*/5 */2 31 * *"));
        $this->assertTrue(rCycle::validate("*/5 */2 31 1 *"));
        $this->assertTrue(rCycle::validate("*/5 */2 31 12 *"));
        $this->assertTrue(rCycle::validate("*/5 */2 31 12 1"));
        $this->assertTrue(rCycle::validate("*/5 */2 31 12 7"));
        $false_case=array(
                   "60 * * * *",
                   "*/60 * * * * *",
                   "",
                   "*",
                   "60",
                   "59 24 * * *",
                    "*/5 */2 31 12 9",
                    "*/5 */2 31 13 7",
                    "*/5 */2 32 12 7"
                 );
        foreach ($false_case as $case){
            try{
                $a=false;
               $false_0=rCycle::validate($case);
               $a=true;
            }catch(Exception $e){
                echo $e->getMessage()."\n";
              }
            $this->assertFalse($a);
        }
    }
    
    public function testCycleCurrect(){
        $time=strtotime("2012-08-12 15:30:00");
        $this->assertTrue(rCycle::isCurrect("* * * * *",$time));
        $this->assertTrue(rCycle::isCurrect("*/5 * * * *",$time));
        $this->assertTrue(rCycle::isCurrect("1,3,5,30 * * * *",$time));
    }
    
    
}