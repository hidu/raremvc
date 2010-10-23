你好，这个是demo/hello
<?php echo  fetch("news")?>
<br/>
<?php echo  fetch("list/yule",array('date'=>"这个是作为参数传递进来的。当前时间是".date("Y-m-d H:i:s")));?>

当前文件<?php echo __FILE__;?>
