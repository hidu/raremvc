<div id="article_view">
<center><h2><?php echo $article['title']?></h2>
<a  href="<?php echo url('admin/article?articleid='.$article['articleid']) ?>">edit</a>
</center>
<div>
<?php echo nl2br($article['body']);?>
</div>
</div>
