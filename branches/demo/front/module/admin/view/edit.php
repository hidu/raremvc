<form method="post" class="edit_form">
<?php if($article['articleid']){?>
<a href="<?php echo url("index/view?articleid=".$article['articleid'])?>">查看文章</a>
<?php }?>
<?php echo rHtml::hidden('a[articleid]', $article['articleid']);?>
<table>
<tr>
<th>title:</th>
<td><?php echo rHtml::input('a[title]',$article['title']);?></td>
</tr>
<tr>
<th>body:</th>
<td><?php echo rHtml::textArea('a[body]',$article['body'],"style='width:400px;height:200px'");?></td>
</tr>
<tr>
<th></th>
<td>
<?php echo rHtml::submit("保存");?>
</td>
</tr>
</table>
</form>