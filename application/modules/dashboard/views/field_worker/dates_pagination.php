<?php 
$is_tooday = false;
for($i=0; $i<=6; $i++): ?>
<?php $button = (strtotime($page_start.' + '.$i.' day')==strtotime(date('Y-m-d')))?'Today' : date("d M", strtotime($page_start.' + '.$i.' day')); ?>

<?php $button_class = 'btn-default col-md-1 col-xs-1'; 
if(strtotime($page_start.' + '.$i.' day')==strtotime(date('Y-m-d'))){
	$is_tooday = true;
}

if(strtotime($page_start.' + '.$i.' day')!=strtotime(date('Y-m-d')) && strtotime($page_start.' + '.$i.' day')==strtotime($dashboard_date))
	$button_class = 'btn-success col-md-3 col-xs-2'; 

if(strtotime($page_start.' + '.$i.' day')==strtotime(date('Y-m-d')) && strtotime($page_start.' + '.$i.' day')!=strtotime($dashboard_date))
	$button_class = 'btn-warning col-md-1 col-xs-2'; 

if(strtotime($page_start.' + '.$i.' day')==strtotime(date('Y-m-d')) && strtotime($page_start.' + '.$i.' day')==strtotime($dashboard_date))
	$button_class = 'btn-success col-md-3 col-xs-2'; 

if($i==0 || $i==6)
	$button_class .= " hidden-sm hidden-xs";
if($i==1)
	$button_class .= " hidden-xs";


?>

<a href="#" data-date="<?php echo strtotime($page_start.' + '.$i.' day'); ?>" class="change-dashboard-date btn-rounded p-10 btn btn-s-xs <?php echo $button_class; ?>"><?php echo $button; ?></a>
<?php endfor; ?>
<?php if($is_tooday==false): ?>
	<div style="position: absolute; right: 0px; padding: 0 15px 0 46px; background: #fff;" class="hidden-xs">
	<a href="#" data-date="<?php echo strtotime(date('Y-m-d')); ?>" class="change-dashboard-date btn-rounded p-10 btn btn-s-xs btn-warning col-md-1 col-xs-1">Today</a>
	</div>
	<div style="position: absolute; right: 0px; padding: 0 8px 0 1px; background: #fff;" class="visible-xs">
	<a href="#" data-date="<?php echo strtotime(date('Y-m-d')); ?>" class="change-dashboard-date btn-rounded p-10 btn btn-s-xs btn-warning col-md-1 col-xs-1">Today</a>	
	</div>
<?php endif; ?>
<div class="clear"></div>