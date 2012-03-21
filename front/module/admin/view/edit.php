<form method="post">
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
</form>