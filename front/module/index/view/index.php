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

<div><?php echo $listPage[1];?></div>

</div>

<?php if(isset($_GET['ajax'])){ ?>
<?php rareView::addJs("https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js");?>
<script>
	$('#index_article_list .rPager a').click(function(){
		var url=$(this).attr('href');
		$('#index_article_list').parent('div').load(url);
		return false;
    });
</script>
<?php } ?>