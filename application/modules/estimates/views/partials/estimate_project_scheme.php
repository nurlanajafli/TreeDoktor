
<section class="col-md-12 panel panel-default p-n" style="display:none;">
	<header class="panel-heading" style="cursor: pointer;">Project Scheme
		
	</header>
	<div class="scheme-block" style="display:none; height: 900px;">
		<div class="" style="height: 900px;">

			<div id="map-live-canvas" class="scheme_map"></div>
			
			<div id="map-canvas" class="scheme_map_screen" style="display: none;">
				<div class="imag pull-right" style="margin:5px 0 5px; z-index: 1">
					<?php if(isset($icons) && !empty($icons)) : ?>
						<?php foreach($icons as $key=>$icon) : ?>
							<img id="house_<?php echo $key + 1; ?>" class="imag" src="<?php echo base_url('uploads/scheme_items/' . $icon); ?>"<?php if(strpos($icon, 'input_')) :?><?php echo ' data-type="' . str_replace(array('zinput_', '.png'), '', $icon) . '"' ; ?><?php endif; ?>>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
				<div class="clearfix"></div>
				<div id="container"></div>
			</div>
			
		</div>
		<?php /*
		<input type="hidden" name="estimate_scheme" id="estimate_scheme">
		<input type="hidden" name="estimate_picture" id="estimate_picture">
		*/ ?>
		<input type="hidden" id="scheme_lat" name="scheme_lat" value="<?php echo (isset($estimate_data))?$estimate_data->lat:$lead->latitude; ?>">
		<input type="hidden" id="scheme_lon" name="scheme_lon" value="<?php echo (isset($estimate_data))?$estimate_data->lon:$lead->longitude; ?>">

		
		
		<?php if(isset($estimate_data->estimate_scheme) && $estimate_data->estimate_scheme) : ?>
		
			<input type="hidden" id="estimate_scheme_source" name="estimate_scheme_source" value='<?php echo $estimate_data->estimate_scheme; ?>'>

		<?php elseif(isset($draft_scheme) && $draft_scheme) :?>
			
			<input type="hidden" id="estimate_scheme_source" name="estimate_scheme_source" value='<?php echo $draft_scheme; ?>'>
		
		<?php else : ?>

			<input type="hidden" id="estimate_scheme_source" name="estimate_scheme_source" value=''>

		<?php endif; ?>

		<div style="clear:both;"></div>
	</div>
</section>


<script async src="<?php echo base_url('/assets/js/modules/estimates/estimate_scheme.js?v.1.11'); ?>"></script>

<style type="text/css">
      html, body, #map-canvas { height: 100%; margin: 0px; padding: 0px; }
      #container .imag{ z-index: 1; }
      .map_screen{ 
      	position: absolute;
      	height: 856px;
      	width: 100%;
      }
</style>
