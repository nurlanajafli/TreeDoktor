Logout not in office <?php echo $date; ?>:
<ul>
<?php foreach($data as $k=>$v) : ?>
	<li>
		<a href="https://www.google.com/maps/search/?api=1&query=<?php echo $v->logout_lat; ?>,<?php echo $v->logout_lon;?>" target="_blank"><?php echo $v->name;?></a>
	</li>
<?php endforeach;?>
</ul>
