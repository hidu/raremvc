<pre>
将index/index 的内容通过ajax 载入到下面的空位
index/index的程序不需要做任何修改，会判断出是ajax 而不需要模板
该特性在ajax 分页的时候非常有用。
</pre>
<a href="#" onclick="$('#ajaxDiv').load('<?php echo url('index/index')?>');return false;">gogogo</a>

<div style="border:2px solid #B7D1F2;" id="ajaxDiv">
预留的空位
</div>
<?php use_helper("test");test();?>