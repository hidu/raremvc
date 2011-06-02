<?php
/**
 *@copyright rareMVC 
 *@author duwei
 *通用工具类
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
   
   public static function cutStr($str,$length=50){
       preg_match_all("/./us", $str, $ar);
       $newstring = join("", array_slice($ar[0], 0, $length));
       return $newstring;
   }
  
  public static function addslashesDeep($value)
  {
    return is_array($value) ? array_map(array('rTookit', 'addslashesDeep'), $value) : addslashes($value);
  }
  
  public static function stripslashesDeep($value)
  {
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
      header_remove("Pragma");
      header_remove("Cache-Control");
      header_remove('Expires');
      header("Content-Type: text/javascript;charset=".rareConfig::get('charset','UTF-8'));
  }
  
  /**
   * 将字符串按照中英文逗号，换行符进行拆分，返回数组或者 空格链接的字符串
   * @param string $str
   * @param boolean $asArray
   */
  public static function str2Words($str,$asArray=true){
     $str=trim(preg_replace(array("/[，,]+/","/\s+/"), " ", $str));
     if($asArray)return explode(" ", $str);
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
}