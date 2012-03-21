
<?php slot_start('slot1'); ?>
恩 不错，这个会显示在左侧！
<?php slot_end();?>
你好，现在的是：<?php echo date('Y-m-d H:i:s'); ?>

<?php foreach ($listPage[0] as $article){?>
<div>
 <div><h2><?php echo $article['articleid']; ?>、
       <a href="<?php echo url('index/view?articleid='.$article['articleid'])?>"><?php echo $article['title']?></a>
      </h2>
 </div>
</div>
<?php }?>

<div><?php echo $listPage[1];?></div>