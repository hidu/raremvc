<ul class="ul-1">
<li>cateid</li>
<li>cateName</li>
<li>pinyin</li>
<li></li>
</ul>
 <?php foreach ($listPage[0] as $one){?>
 <ul class="ul-1">
  <li><?php echo $one['cateid']?></li>
  <li>
 <form method="post">
 <?php echo rHtml::hidden('method', 'edit');?>
 <?php echo rHtml::hidden('c[cateid]',$one['cateid'])?> 
 <?php echo rHtml::input('c[catename]',$one['catename'],"style='width:70px'")?>
 <?php echo rHtml::input('c[pinyin]',$one['pinyin'],"style='width:70px'")?>
 <?php echo rHtml::submit('保存');?>
 </form>
  </li>
  <li>
   <form method="post" onsubmit="return confirm('确定删除？')">
   <?php echo rHtml::hidden('method', 'delete');?>
   <?php echo rHtml::hidden('cateid',$one['cateid'])?> 
   <?php echo rHtml::submit("删除");?>
   </form>
  </li>
</ul>
<?php }?>
<br/>
<form method="post">
<?php echo rHtml::hidden('method', 'edit');?>
 名称：<?php echo rHtml::input('c[catename]','',"style='width:70px'")?>
 拼音：<?php echo rHtml::input('c[pinyin]',"","style='width:70px'")?>
 <?php echo rHtml::submit('添加');?>
</form>
