<?php
$cates=service_category::getAll();
if(!$cates)return;
$cur_cateID=isset($_GET['cateid'])?$_GET['cateid']:0;
?>
<div class="cpanel" id="c_cate">
<div class="cpanel_title">文章分类</div>
<div class="cpanel_content">
<ul>
<?php foreach ($cates as $cateID=>$cate){?>
<li <?php if($cur_cateID==$cateID){?>class="cur"<?php }?>>
  <a href="<?php echo url("index?cateid=".$cateID)?>">
     <?php echo $cate['catename']?>
  </a>
</li>
<?php }?>
</ul>
</div>
</div>