<form method="post" style="margin:10% 20% 20% 35%">
<b>login please!</b>
<p>user:<?php echo rHtml::input('user',"admin");?></p>
<p>&nbsp;psw:<?php echo rHtml::password('psw','admin');?></p>
<p style="margin-left:50px">
 <?php echo rHtml::submit('登录');?>
 <?php echo rHtml::input_reset('重置');?>
</p>
</form>