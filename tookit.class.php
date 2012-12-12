<?php
/**
 *通用工具类
 *@copyright rareMVC 
 *@author duwei
 *@package addon
 */
class rTookit{
   
   /**
    * 支持html的字符串截取
    * @param string $html
    * @param int $length
    */
   public static function cutHtml($html,$length=300){
      $html=strip_tags($html);
      return self::cutStr($html,$length);
   }
   
   public static function cutStr($str,$length=50,$reverse=false){
       preg_match_all("/./us", $str, $ar);
       if(!$reverse){
           return join("", array_slice($ar[0], 0, $length));
        }
       $len=count($ar[0]);
       $start=max(0,$len-$length);
       return join("", array_slice($ar[0], $start,$length));
   }
  
  public static function addslashesDeep($value){
    return is_array($value) ? array_map(array('rTookit', 'addslashesDeep'), $value) : addslashes($value);
  }
  
  public static function stripslashesDeep($value){
    return sfToolkit::stripslashesDeep($value);
  }
  
  /**
   * 设置 magic_quotes_gpc
   * 
   * @param boolean $isQuote
   */
  public static function set_magic_quotes_gpc($isQuote=0){
    $isQuote=strtolower($isQuote);
    if(!$isQuote || $isQuote=='off')$isQuote=0;
    if($isQuote)$isQuote=1;
    if(get_magic_quotes_gpc()==$isQuote)return;
    
    if(!$isQuote){
        $tmp=sfToolkit::stripslashesDeep($_POST);
        foreach ($tmp as $k=>$v){
          $_POST[$k]=$v;
         }
        $tmp=sfToolkit::stripslashesDeep($_GET);
        foreach ($tmp as $k=>$v){
          $_GET[$k]=$v;
         }
        $tmp=sfToolkit::stripslashesDeep($_COOKIE);
        foreach ($tmp as $k=>$v){
          $_COOKIE[$k]=$v;
         }
        $tmp=sfToolkit::stripslashesDeep($_REQUEST);
        foreach ($tmp as $k=>$v){
          $_REQUEST[$k]=$v;
         }
     }else{
        $tmp=self::addslashesDeep($_POST);
        foreach ($tmp as $k=>$v){
          $_POST[$k]=$v;
         }
        $tmp=self::addslashesDeep($_GET);
        foreach ($tmp as $k=>$v){
          $_GET[$k]=$v;
         }
        $tmp=self::addslashesDeep($_COOKIE);
        foreach ($tmp as $k=>$v){
          $_COOKIE[$k]=$v;
         }
        $tmp=self::addslashesDeep($_REQUEST);
        foreach ($tmp as $k=>$v){
          $_REQUEST[$k]=$v;
         }
     }
  }
  
  /**
   *该方法用户修正ie6一些版本不能正确识别程序动态输出的js的问题。(表现为第一次不正常，刷新后运行正常)
   *在动态js的action中调用该方法即可。 
   */
  public static function dynamic_js_header(){
      header("Pragma:");
      header("Cache-Control:");
      header('Expires:');
      header("Content-Type: text/javascript;charset=".rareConfig::get('charset','UTF-8'));
  }
  
  /**
   * 将字符串按照中英文逗号，换行符进行拆分，返回数组或者 空格链接的字符串
   * @param string $str
   * @param boolean $asArray
   */
  public static function str2Words($str,$asArray=true){
     $str=trim(preg_replace(array("/[，,]+/","/\s+/"), " ", $str));
     if($asArray){
        $str=explode(" ", $str);
        qArray::removeEmpty($str,true);
     }
     return $str;
  }
  
 public static function thumbImage($srcPath,$distPath,$width,$height){
       $imageSize=getimagesize($srcPath);
       $w=$imageSize[0];
       $h=$imageSize[1];
       $saveWidth=$width;
       $saveHeight=$height;
       
       switch($imageSize[2]){//取得背景图片的格式
            case 1:$image = imagecreatefromgif($srcPath);break;
            case 2:$image = imagecreatefromjpeg($srcPath);break;
            case 3:$image = imagecreatefrompng($srcPath);break;
            case 6:$image = imagecreatefromwbmp($srcPath);break;
            default:return null;//不支持的格式则不做处理
        }
       $distImage=imagecreatetruecolor($saveWidth,$saveHeight);
       imagecopyresampled($distImage,$image,0,0,0,0,$saveWidth,$saveHeight,$w,$h);
       imagepng($distImage,$distPath);
       imagedestroy($distImage);
    }
    
  /**
   * url安全的base64_encode +/=会替换为-_~
   * @param $str
   */
    function safe64Encode($str) {
       return strtr(base64_encode($str), '+/=', '-_~');
    }
   /**
    * 对使用safe64Encode编码处理的字符串decode
    * @param $str
    */
   function safe64Decode($str) {
      return base64_decode(strtr($str, '-_~', '+/='));
    }  

   /**
    * 加密字符串
    * @param string $str
    * @param string $key 密钥
    */ 
  public static function encrypt($str, $key=null){
      $block = mcrypt_get_block_size('des', 'ecb');
      $pad = $block - (strlen($str) % $block);
      $str .= str_repeat(chr($pad), $pad);
  
      $str=mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
      $str=self::safe64Encode($str);
      return $str;
  }
  
  /**
   * 解密字符串
   * @param string $str
   * @param string $key 密钥
   */
 public static function decrypt($str, $key=null){  
      $str=self::safe64Decode($str);
      $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
      $block = mcrypt_get_block_size('des', 'ecb');
      $pad = ord($str[($len = strlen($str)) - 1]);
      return substr($str, 0, strlen($str) - $pad);
  } 
}