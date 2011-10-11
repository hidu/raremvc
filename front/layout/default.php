<!DOCTYPE html>
<html>
<head>
<?php rareView::include_title();?>
<?php rareView::include_js_css()?>
</head>
<body>
<?php echo fetch('component1','time='.date('H:i:s'));?>
<div style='width:180px;float:left;border:1px solid blue;min-height:400px'><?php echo slot_get('slot1')?></div>
<div style='margin-left:190px;min-height:400px;border:1px solid blue;'><?php echo $body;?></div>
</body>
</html>
