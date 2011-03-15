<?php
/**
 *@copyright rareMVC 
 *@author duwei
 *
 */
abstract class rareCache{
   abstract public function has($key);
   abstract public function get($key,$default=null);
   abstract public function set($key,$data,$lifetime=null);
   abstract public function remove($key);
}
