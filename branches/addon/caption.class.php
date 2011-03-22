<?php
/**
 *图像验证码 
 * @author duwei
 *
 */
class rCaption{
    private static $key="rareCaption";   
    
    /**
     * 检查验证码是否正确
     * @param string $caption
     */
    public static function check($caption){
      return isset($_SESSION[self::$key]) && $caption==strtolower($_SESSION[self::$key]);
    }
    
    public static function clean(){
         unset($_SESSION[self::$key]);
    }
    
    /**
     *获取验证码图像 
     */
    public static function getImage(){
        ob_clean();
        header("Content-Type:image/png");
        header('Expires: Fri, 22 Mar 1970 18:59:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        $code=strtoupper(strtr(substr(md5(uniqid()),0,4),array('o'=>'p','0'=>'G')));
        $_SESSION[self::$key]=$code;
        
        $width=60;
        $height=30;     
        $image = imagecreate(60, 25);
        $background = imagecolorallocate($image,3,255,255);
        imagecolortransparent($image,$background);
        for($i=0; $i<80; $i++){
            $color = imagecolorallocate($image, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
            imagesetpixel($image, mt_rand(0, $width), mt_rand(0,$height), $color);
        }
        $colors=array();        
        $colors[]  = imagecolorallocate($image,243,97,97);
        $colors[]  = imagecolorallocate($image,83,104,189);
        $colors[]  = imagecolorallocate($image,54,90,39);
        
        $len  = strlen($code);
        for($num=0; $num<$len; $num++){
            imagestring($image, 5, 5+12*$num+mt_rand(0,4), 3+mt_rand(0,4), $code[$num], $colors[mt_rand(0,2)]);
         }
        imagepng($image);
        imagedestroy($image);
        die;
    }

}
