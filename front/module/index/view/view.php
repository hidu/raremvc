<center><h2><?php echo $article['title']?></h2>
<a  href="<?php echo url('admin/edit?articleid='.$article['articleid']) ?>">edit</a>
</center>
<div>
<?php echo $article['body']?>
</div>