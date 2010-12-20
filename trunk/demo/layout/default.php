<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php rareView::include_title()?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="keywords" content="rareMVC php framework"/>
<meta name="description" content="rareMVC demo,这个是默认的模板"/>
<?php rareView::addCss("a.js")?>
<?php rareView::addCss("b.js")?>
<?php rareView::include_js_css()?>
</head>
    <body>
    <div>
    <ol>
       <li><a href="<?php echo url('index/index?a=1&b=2')?>">首页</a></li>
       <li><a href="<?php echo url('demo/hello?a=2&b=1')?>">hello</a></li>
       <li><a href="<?php echo url('index/a')?>">内部跳转：forward</a></li>
       <li><a href="<?php echo url('index/b')?>">渲染其他视图</a></li>
       <li><a href="<?php echo url('demo/ajax')?>">模板与ajax</a></li>
       <li><a href="<?php echo url('demo/rest')?>">rest</a></li>
       <li><a href="<?php echo url('demo/thisis404')?>">这是一个404</a></li>
           <li><a href="<?php echo url('demo/thisis500')?>">这是一个500</a></li>
           <li><a href="<?php echo url('demo/thisis500two')?>">这是一个500two</a></li>
    </ol>
    </div>
    <?php echo $body;?>
    </body> 
</html>