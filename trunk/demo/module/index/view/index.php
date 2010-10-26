<?php if(isset($date)){?>
<div>今天的日期是：<?php echo $date;?></div>
<?php }?>

<div><?php echo $msg;?></div>
<?php echo url('index/index?a=1&b=2','js')?>
<br/>
<?php echo url('demo/index?a=1&b=2','js')?>

<form action="<?php echo url('index/loginCheck')?>" method="post" id="loginForm" autocomplete="off">
<table>
<caption>登录示例</caption>
<tr>
   <th>用户名：</th>
   <td><input type="text" name="name"></td>
</tr>
<tr>
   <th>密  码：</th>
   <td><input type="password" name="psw"></td>
</tr>
<tr>
 <th></th>
 <td><input type="submit" value="登录"></td>
</tr>
</table>
正确的帐号是：user,密码是: passwd
</form>
<script>
$().ready(function(){
	$("#loginForm").ajaxForm({
		dataType:"json",
		success:function(data){
		  alert(data.info);
         if(data.status==1){
               alert("你可以做其他的事情！");
            }    
		}
	   });
});
</script>