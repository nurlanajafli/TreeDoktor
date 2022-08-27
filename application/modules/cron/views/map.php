<?php $this->load->view('includes/header'); ?>
<script async src="<?php echo base_url('assets/js/StyledMarker.js'); ?>"></script>
<script async src="<?php echo base_url('assets/js/label.js'); ?>"></script>
 
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
<link href="https://vitalets.github.io/x-editable/assets/bootstrap-datetimepicker/css/datetimepicker.css" rel="stylesheet">
<script src="<?php echo base_url('assets/js/gmaps-markerwithlabel-1.9.1.js'); ?>" type="text/javascript"></script>

<style>
	.gm-style-iw{overflow: visible!important;}.popover{z-index: 99999;}
	div>.gm-style-iw, .gm-style-iw>div, .gm-style-iw>div>div{font-size: 20px; overflow: visible!important;}
	.pinlabel {
           color: #000;
           background-color: white;
           border: 1px solid #000;
           font-family: "Lucida Grande", "Arial", sans-serif;
           font-size: 12px;
           text-align: center;
           white-space: nowrap;
           padding: 2px;
           background-color: #fff;
       }
</style>

<link href="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/css/bootstrap-editable.css'); ?>" rel="stylesheet">
<section class="scrollable p-sides-15 p-n mapper" style="top: 9px;-webkit-transform: translate3d(0,0,0);">
	<?php echo $map['html']; ?>
	<div class="open" style="position: initial;">
		<ul class="dropdown-menu on" style="left: auto; right: 5px; overflow: auto; top: 0;">
			 
		</ul>
	</div>
</section>
 
<script>
	
	$(document).ready(function(){
		setTimeout(function(){
		<?php foreach($polygons as $k=>$v) : //echo ''; var_dump($v); die;?>
			
				<?php if(!$v['text']) :?>
					<?php break; ?>
					<?php endif; ?>
				var bounds = new google.maps.LatLngBounds();
				var i; 
				var polygonCoords = polygons[<?php echo $k; ?>].getPaths().getArray()[0].j;
				
				for (i = 0; i < polygonCoords.length; i++) {
				  bounds.extend(polygonCoords[i]);
				}


				var marker = new MarkerWithLabel({
					   position:  bounds.getCenter(),
					   map: map,
					   icon: 'data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==',
					   labelContent: '<?php echo $v['text'];?>',
					   labelAnchor: new google.maps.Point(0, 0),
					   // the CSS class for the label
					   labelClass: "pinlabel",
					   labelInBackground: true
					 });
			//return false;
			
		<?php endforeach; ?>
		}, 300);
	});
</script>   
<?php $this->load->view('includes/footer'); ?>
