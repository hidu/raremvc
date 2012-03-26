<?php
$articles=service_article::getLast(10);
?>
<div class="cpanel">
<div class="cpanel_title">最新文章</div>
<div class="cpanel_content">
<ul class="ul-2">
<?php foreach ($articles as $article){?>
<li>
   <span class="right" style="color:gray">(<?php echo date("m-d",$article['ctime']);?>)</span>
   <span><a href="<?php echo url('index/view?articleid='.$article['articleid'])?>" title="<?php echo h($article['title'])?>"><?php echo $article['title']?></a></span>
</li>
<?php }?>
</ul>
</div>
</div>

