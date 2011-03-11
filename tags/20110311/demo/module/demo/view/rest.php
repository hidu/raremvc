<form method="post" id="restForm" action="<?php echo url('')?>">
<input type="text" name="name">
<input type="submit" value="提交">
</form>
<script>
$().ready(function(){
    $("#restForm").ajaxForm({
    	dataType:"json",
        success:function(data){
          alert(data.i);
        }
       });
});
</script>