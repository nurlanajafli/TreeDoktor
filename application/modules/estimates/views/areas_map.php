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
	<section class="panel panel-default p-n">
		<header class="panel-heading">Filter
			<div class="pull-right" style="">
				<form id="dates" method="post" action="<?php echo base_url('estimates/estimates_by_areas'); ?>" class="input-append m-t-xs">
					
					<label>
						<input name="from" class="datepicker form-control date-input-client from text-center" type="text" readonly
                            value="<?php if ($from) : echo getDateTimeWithDate($from, 'Y-m-d 00:00:00');
                            else : echo date(getDateFormat(), (time() - 86400 * 7)); endif; ?>">
					</label>
					— &nbsp;&nbsp;
					<label>
						<input name="to" class="datepicker form-control date-input-client to text-center" type="text" readonly
                            value="<?php if ($to) : echo getDateTimeWithDate($to, 'Y-m-d 23:59:59');
                            else : echo date(getDateFormat()); endif; ?>">
					</label>
					<select id="users" name="user_id" class="form-control date-input-client user pull-left" style="width: 170px;margin-top: 1px;">
						<option>Choose Estimator</option>
						<?php foreach($users as $key=>$val) : ?>
							<option <?php if(isset($user_id) && $user_id == $val['id']) : ?>selected="selected"<?php endif; ?> value="<?php echo $val['id']?>"><?php echo $val['firstname'];?> <?php echo $val['lastname'];?> </option>
						<?php endforeach; ?>
					</select>
					<input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-bottom: 5px;" value="GO!">
					<div class="clear"></div>
				</form>
			</div>
			<div class="clear"></div>
            <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
		</header>
		<script>
			$(document).ready(function () {
				$('.datepicker').datepicker({format: $('#php-variable').val()});
			});
		</script>
	</section>
	<?php echo $map['html']; ?> 
		<div class="affix" style="top: 18%; right: 105px;">
			<ul class="nav navbar-nav navbar-right m-n hidden-xs nav-user">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle p-10 bg-white b-a" data-toggle="dropdown"
					   style="padding: 5px 10px;">
						<i class="fa fa-gears"></i>
					</a>
					<ul class="dropdown-menu on animated fadeInRight scrollable" id="note-list" style="right: -70px; height: 500px;">
						<span class="arrow top"></span>
							<li>
								<a onclick="setPins();" class="visiblePin" href="javascript:void(0);" >
									<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGhlaWdodD0iMjAgIiB3aWR0aD0iMjAiIHZpZXdCb3g9IjAgMCA0ODAgNDgwIj48cGF0aCBmaWxsPSIjODFiYTUzIiBzdHJva2U9IiMwMDAwMDAiIHN0cm9rZS13aWR0aD0iNSIgZmlsbC1ydWxlPSJub256ZXJvIiBtYXJrZXItc3RhcnQ9IiIgbWFya2VyLW1pZD0iIiBtYXJrZXItZW5kPSIiIGlkPSJzdmdfMTYiIGQ9Ik0xMS4xODYxNTU2NDkwMDkwODgsNDc2LjA4ODUwNDE2NzYyNjcgTDEwMy4xNTg3MzE4Mjk0NjY0MywzMTEuOTYxNTI2OTcyMTc3MzUgTDEwMy4xNTg3MzE4Mjk0NjY0MywzMTEuOTYxNTI2OTcyMTc3MzUgQzUuNzIxMTY3ODc2OTkzOTM2NSwyNjIuNDUxNzAxOTI0OTM5NyAtMjIuMDgyNTE4NDI2ODk5MDkyLDE2OS4xODU3NzYzODkwNzM1NCAzOC45MjQ1NDMyNDE2MjExLDk2LjQ5MzA5MzAwNTA5NjY0IEM5OS45MzA0NjAxNDQxMDA3NiwyMy44MDA4MzA0NTY1NzYwNjUgMjI5Ljc4Mzk4ODU4NDY1NzA0LC00LjUyNzE5MzIxMTM2MjIxIDMzOC45MTQ2MzIyODA4MjMzLDMxLjA0ODU2NzYwNjY2MjU3IEM0NDguMDQ1MjQ0NjEzNTMwMjYsNjYuNjI0MTI1ODAwMjQzNjUgNTAwLjc0MTM2NzMyOTEzNjI3LDE1NC40NTk4ODY4ODkyMDI1OCA0NjAuNjUyNjAwNDA1MjY5NTQsMjMzLjk3MTg2NTc2NjU5ODg1IEM0MjAuNTY1OTUwNTE0NTI2OCwzMTMuNDgzODEzNDcwOTc2NyAzMDAuODE1ODc2NTE0Mjg0OCwzNTguNjQ5NzI4Mjg4OTQ0NCAxODQuMDAwMDU3NDk5NzQ5NjYsMzM4LjMxNTQ3MzkxMDY3NiBMMTEuMTg2MTU1NjQ5MDA5MDg4LDQ3Ni4wODg1MDQxNjc2MjY3IHoiIHN0eWxlPSJjb2xvcjogcmdiKDAsIDAsIDApOyIgY2xhc3M9IiIvPjx0ZXh0IHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjQwIDIzMCkiIGZpbGw9IiMjMDAwIiBzdHlsZT0iZm9udC1mYW1pbHk6IEFyaWFsLCBzYW5zLXNlcmlmO2ZvbnQtd2VpZ2h0OmJvbGQ7dGV4dC1hbGlnbjpjZW50ZXI7IiBmb250LXNpemU9IjEyMCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+PC90ZXh0Pjwvc3ZnPg=="> Add/Remove Markers
								</a>
							</li>
						<?php $i = 0; ?>
						<?php foreach($rating as $k=>$v) : ?>
							<li>
								 <a onclick="setPolygon(<?php echo $k; ?>);" class="polygons-<?php echo $k;?>" href="javascript:void(0);" ><span style="border: 1px solid #000;display: inline-block;width: 18px;background: #<?php echo $v; ?>">&nbsp;</span> 
									 <?php if(!isset($rating[$k+5])) : ?>
										> 90 %
									<?php else : ?>
										<?php echo $i; ?>% - <?php echo $k; ?>%
									<?php endif; ?> 
								 </a>
							</li>
						
							<?php $i = $i+5;?>
						 
						<?php endforeach; ?>
						
					</ul>
				</li>
		</ul>
	</div>
</section>
 
<script>
	var pollygons = <?php echo json_encode($visibleSet); ?>;
	function setPolygon(jkey)
	{ 
		if(pollygons[jkey].length)
		{
			var visible = true;
			
			$.each(pollygons[jkey], function(key, val){
			
				var num = val-1;
				if(!key)
				{
					if(polygons[val-1].getVisible())
						visible = false;
				}
				polygons[num].setVisible(visible);
				markers[num].setVisible(visible);
			});
			if(!visible)
				$('.polygons-'+jkey).parent().css('text-decoration', 'line-through');
			else
				$('.polygons-'+jkey).parent().css('text-decoration', 'none');
		}
		return false;
	}
	function setPins()
	{
		var visible = true;
		$('.visiblePin').parent().css('text-decoration', 'none');
		if(marker_0.getVisible())
		{
			visible = false;
			$('.visiblePin').parent().css('text-decoration', 'line-through');
		}
		$.each(markers, function(key, val){
			markers[key].setVisible(visible);
		}); 
		return false;
	}
	
	<?php /*
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
	*/ ?>
</script>   
<?php $this->load->view('includes/footer'); ?>
