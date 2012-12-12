<?php
/**
 * 
 * Mozilla/5.0 (X11; U; Linux x86_64; zh-CN; rv:1.9.2.17) Gecko/20110428 Fedora/3.6.17-1.fc13 Firefox/3.6.17
 * Mozilla/5.0 (X11; Linux x86_64; rv:2.0.1) Gecko/20100101 Firefox/4.0.1
 * Mozilla/5.0 (X11; Linux x86_64; rv:5.0) Gecko/20100101 Firefox/5.0
 * 
 * Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.100 Safari/534.30
 * Opera/9.80 (X11; Linux x86_64; U; zh-cn) Presto/2.7.62 Version/11.01 
 * 
 * 
 * ================================
 * 
 * Mozilla/5.0 (Windows; U; Windows NT 5.2; zh-CN; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16
 * 
 * Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.648; .NET CLR 3.5.21022; TheWorld)
 * Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Maxthon 2.0)
 * Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.648; .NET CLR 3.5.21022; 360SE)
 * Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1
 * 
 * =================================
 * iphone 3.0
 * Mozilla/5.0 (iPhone; U; CPU iPhone OS 3_0 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Version/4.0 Mobile/7A341 Safari/528.16
 * 
 * @author duwei
 *@package addon\other
 */

class rClientBrowser{
  private $rules=array(
                     'firefox' => array("/(mozilla).*firefox\/([\w.]+)/i","/(linux|windowsce|windows).*rv/i"),   
                      'chrome' => array("/(webKit).*chrome[\/]([\w.]+)/i","/mozilla.*\(\w+)/i"),   
                      'safari' => array("/(webKit).*version[\/]([\w.]+)(?:.*safari)/i","/\((\w+);\s+u;/i"),   
                       'opera' => array("/(opera)(?:.*version)?[ \/]([\w.]+)/i","/(j2me|linux|windows)/i"),   
                        'msie' => array("/(msie) ([\w.]+)/i","/(windows)/i"),   
                   'konqueror' => array("/(konqueror)[\/]([\w.]+)/i","/(linux|windows)/i"),   
                    'googlebot' => array("/(googlebot)(?:[\w-]*)[\/]([\w.]+)/i"),   
                  'baiduspider' => array("/(baiduspider)/i"),   
                     );
  private $browser=array();
  private $ua;
                     
                     
  public function __construct($ua=null){
    if($ua==null)$ua=$this->getUserAgent();
    $this->ua=$ua;
    foreach ($this->rules as $name=>$rule){
       if(preg_match($rule[0], $ua,$matches)){
            $this->browser[$name]['version']=$matches[2];
             if(preg_match($rule[1], $ua,$matches2)){
                $this->browser[$name]['platform']=$matches2[1];
               }
            continue;
        }
    }
  }
  
  public function isWindows(){
    return preg_match("/(windows)/i", $this->ua);
  }
  
  public function isLinux(){
    return preg_match("/(linux)/i", $this->ua);
  }
  
  public   function getUserAgent(){
    return self::_getServerInfo('HTTP_USER_AGENT');
  }
  
  public function is($browserName){
    $browserName=strtolower($browserName);
    return isset($this->browser[$browserName])?$this->browser[$browserName]:false;
  }
  
  public function ifFirefox(){
    return $this->is('firefox');
  }
  
  public function isMsIE(){
    return $this->is('msie');
  }
  
  private  function _getServerInfo($key){
    return isset($_SERVER[$key])?$_SERVER[$key]:null;
  }
  
  public  function getBrowser(){
    
  }
  
  
}