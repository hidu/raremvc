<div id="index_article_list">
<?php foreach ($listPage[0] as $article){?>
<div class="article">
 <div class="title">
 <h2><a href="<?php echo url('index/view?articleid='.$article['articleid'])?>"><?php echo $article['title']?></a></h2>
 </div>
 <div>
 <?php echo nl2br($article['body']);?>
 </div>
</div>
<?php }?>
</div>

<div><?php echo $listPage[1];?></div>