<?php
class rUpload{
    /**
     *从$_FILE中获取格式化的file
     *html标签支持
     *<input type=file name='f'> 
     *<input type=file name='f[]'>
     * 不支持多维数组：
     *<input type=file name='f[a][]'> 
     */
    public static function get_files(){
        if(!$_FILES)return array();
        //当不为数组时直接返回
       $files=array();
       foreach ($_FILES as $key=>$file){
           if(is_string($file['name'])){
               $pathInfo=pathinfo($file['name']);
               $file['ext']=$pathInfo['extension'];
               $files[$key]=$file;
               continue;
             }
           $count=count($file['name']);
          for($i=0;$i<$count;$i++){
             $pathInfo=pathinfo($file['name'][$i]);
             $files[$key][$i]['name']     =  $file['name'][$i];
             $files[$key][$i]['type']     =  $file['type'][$i];
             $files[$key][$i]['tmp_name'] =  $file['tmp_name'][$i];
             $files[$key][$i]['error']    =  $file['error'][$i];
             $files[$key][$i]['size']     =  $file['size'][$i];
             $files[$key][$i]['ext']      =  $pathInfo['extension'];
           }
       }
       return $files;
    }
    /**
     *获取常用图片的mime格式 
     */
    public static function getImageMimeTypes(){
       $mimeTypes = array("image/gif"=>"gif","image/pjpeg"=>"jpg",
                          "image/jpeg"=>"jpg","image/png"=>"png",
                          "image/x-png"=>"png","image/bmp"=>"bmp"); 
        return $mimeTypes;
    }
    /**
     * 检查指定的mime格式是否是支持的图片格式
     * @param string $mime
     */
    public static function checkIsImageByMime($mime){
        if(!$mime)return false;
        if(is_array($mime) && isset($mime['type']))$mime=$mime['type'];
         $mimeTypes=self::getImageMimeTypes();
         return array_key_exists($mime, $mimeTypes);
    }
}