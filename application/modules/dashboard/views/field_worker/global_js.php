<script type="text/javascript">
	window.map_origin = JSON.parse('<?php echo (isset($origin))?json_encode(explode(",", $origin)):json_encode([]); ?>');
	window.map_destination = JSON.parse('<?php echo (isset($destination))?json_encode(explode(",", $destination)):json_encode([]); ?>');
	
	window.map_waypoints = [
    <?php if(isset($waypoints)): ?>
    <?php foreach ($waypoints as $key => $point): ?>
    	<?php echo '{location: "'.$point.'", stopover:true}'; ?><?php if($key-1!=count($waypoints)): //countOk ?>,<?php endif; ?>
    <?php endforeach; ?>
    <?php endif; ?>
    ];

    window.map_waypoints_ext = [
    <?php if(isset($waypoints)): ?>
    <?php foreach ($waypoints as $key => $point): ?>
    	<?php echo '{location: "'.$point.'", stopover:true, lat: '.explode(",", $point)[0].', lng:'.explode(",", $point)[1].'  }'; ?><?php if($key-1!=count($waypoints)): //countOk ?>,<?php endif; ?>
    <?php endforeach; ?>
    <?php endif; ?>
    ];
</script>