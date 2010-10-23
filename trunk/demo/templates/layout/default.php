<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>rare demo</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="keywords" content="rareMVC php framework"/>
<meta name="description" content="rareMVC demo,这个是默认的模板"/>
<script type="text/javascript" src="<?php echo public_path('js/jquery.js')?>"></script>
<script type="text/javascript" src="<?php echo public_path('js/jqueryform.js')?>"></script>
</head>
    <body>
    <div>
    <ol>
       <li><a href="<?php echo url('index/index')?>">首页</a></li>
       <li><a href="<?php echo url('demo/hello')?>">hello</a></li>
       <li><a href="<?php echo url('index/a')?>">内部跳转：forward</a></li>
       <li><a href="<?php echo url('index/b')?>">渲染其他视图</a></li>
       <li><a href="<?php echo url('demo/ajax')?>">模板与ajax</a></li>
    </ol>
    </div>
    <?php echo $content;?>
    </body> 
</html>