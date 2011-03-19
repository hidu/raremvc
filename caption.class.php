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
        
        $red = imagecolorallocate($image,0xf3,0x61,0x61);
        $blue = imagecolorallocate($image,0x53,0x68,0xbd);
        $green = imagecolorallocate($image,0x36,0x5A,0x27);
        $colors = array($red, $blue, $green);
        $len  = strlen($code);
        for($num=0; $num<$len; $num++){
            imagestring($image, 5, 5+12*$num+mt_rand(0,4), 3+mt_rand(0,4), $code[$num], $colors[mt_rand(0,2)]);
         }
        imagepng($image);
        imagedestroy($image);
        die;
    }

}