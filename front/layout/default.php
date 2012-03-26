<!DOCTYPE html>
<html>
<head>
<?php rareView::include_title();?>
<?php rareView::include_js_css()?>
</head>
<body>
<div id="top-nav"></div>
<div class="wrap" id="bd">
  <div id="header">
    <h1><a href="<?php echo url('index')?>">demo</a></h1>
  </div>
  <div  id="content">
    <div class="cpanel" id="center">
    <div class="cpanel_title" id="headBar">
       <div class="left"><a href="<?php echo url('index')?>">首页</a><?php echo slot_get('nav_left');?></div>
       <div class="right">
         <a href="<?php echo url('admin/cate')?>">cate</a>
         <a href="<?php echo url('admin/edit')?>">post</a>
       </div>
   </div>
    <?php echo $body;?>
    </div>
    <div id="aside">
     <?php echo fetch('cate');?>
     <?php echo fetch('last');?>
     <?php echo slot_get('aside')?>
    </div>
  </div>
</div>
<div id="footer" class="wrap">rare demo</div>
</body>
</html>