
<button class="btn btn-warning dropdown-toggle"  data-toggle="dropdown">
	<i class="fa fa-filter"></i>
	<span class="caret"  style="margin-left:5px;"></span>
</button>
<div class="dropdown-menu animated fadeInDown searchFrom" style="width: 260px;">
	<span class="arrow top" style="left: 85%;"></span>

	<form name="search" id="search" method="post" action="<?php echo base_url('estimates/search_estimates'); ?>">
		<div class="table-responsive">
			<table class="table m-n">
				<tr>
					<td>
						<label class="pull-left" style="width:48%;"><small>Date From</small>
							<input name="search_estimate_from" class="datepicker form-control input-sm" type="text" placeholder="Date From" readonly
								   value="<?php if(isset($from)) : echo date(getDateFormat(), strtotime($from)); endif; ?>">
						</label>
						
						<label class="pull-right" style="width:48%;"><small>Date To</small>
							<input name="search_estimate_to" class="datepicker form-control input-sm" type="text" placeholder="Date To" readonly
								   value="<?php if(isset($to)) : echo date(getDateFormat(), strtotime($to)); endif; ?>">
						</label>
                        <a href="#" class="pull-right resetDate">Reset Date</a>
					</td>
				</tr>
				<tr>
					<td><small>Select Service</small>
						<select name="search_service_type" class="input-sm form-control" >
							<option value="">Select Service</option>
							<?php foreach($services as $k=>$v) : ?>
								<option <?php if (isset($search_service_type) && $search_service_type == $v->service_id): ?>selected="selected"<?php endif; ?> value="<?php echo $v->service_id;?>"><?php echo $v->service_name;?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<p>Estimates</p>
                        <label class="pull-left" style="width:50%;"><small>Price From</small>
                            <input onchange="/*updateInput(sliderPrice, from, this.value)*/" style="width:90%" name="search_estimate_price_from" type="number" step="any" class="input-sm form-control" placeholder="Estimate Price From"
                                   value="<?php if(isset($search_estimate_price_from)) : echo $search_estimate_price_from; endif; ?>">
                        </label>
                        <label class="pull-right" style="width:50%;"><small>Price To</small>
                            <input onchange="/*updateInput(sliderPrice, to, this.value)*/" style="width:90%" name="search_estimate_price_to" type="number"  step="any" class="input-sm form-control" placeholder="Estimate Price To"
                                   value="<?php if(isset($search_estimate_price_to)) : echo $search_estimate_price_to; endif; ?>">
                        </label>
						<!--<b>$ 0</b>
							<input type="text" class="sliderPrice form-control" value="" >
						<b>$ 10000</b>-->
					</td>
				</tr>
				<tr>
					<td>
						<p>Services</p>
                        <label class="pull-left" style="width:50%;"><small>Price From</small>
                            <input style="width:90%" name="search_service_price_from" type="number" step="any" class="input-sm form-control" placeholder="Service Price From"
                                   value="<?php if(isset($search_service_price_from)) : echo $search_service_price_from; endif; ?>">
                        </label>
                        <label class="pull-right" style="width:50%;"><small>Price To</small>
                            <input style="width:90%" name="search_service_price_to" type="number" step="any" class="input-sm form-control" placeholder="Service Price To"
                                   value="<?php if(isset($search_service_price_to)) : echo $search_service_price_to; endif; ?>">
                        </label>
						<!--<b>$ 0</b>
							<input type="text" class="sliderServicePrice form-control" value="" >
						<b>$ 10000</b>-->
					</td>
				</tr>
				<tr>
					<td><small>Select Estimator</small>
						<select name="search_estimator" class="input-sm form-control" >
							<option value="">Select Estimator</option>
							<?php foreach($estimators as $k=>$v) : ?>
								<option <?php if (isset($search_estimator) && $search_estimator == $v['id']): ?>selected="selected"<?php endif; ?> value="<?php echo $v['id'];?>"><?php echo $v['firstname'];?> <?php echo $v['lastname'];?></option>
							<?php endforeach; ?>
						</select>
						
					</td>
				</tr>
				<tr>
					<td>
						<p><small>Select Team Members</small></p>
						<div class="col-md-12">
							<label class="checkbox-inline p-n"><input type="radio" name="orand" value="or"<?php if(!isset($orand) || (isset($orand) && $orand == 'or')) :?> checked="checked"<?php endif; ?>> OR </label>
							<label class="checkbox-inline p-n"><input type="radio" name="orand" value="and"<?php if(isset($orand) && $orand == 'and') :?> checked="checked"<?php endif; ?>> AND </label>
						</div>
						<select multiple="" name="search_workers[]" class="input-sm form-control search_workers" >
							<??>
							<?php foreach($workers as $k=>$v) : ?>
								<option <?php if ((isset($search_workers) && !empty($search_workers)) && array_search($v['id'], $search_workers) !== FALSE) : ?>selected="selected"<?php endif; ?> value="<?php echo $v['id'];?>"><?php echo $v['firstname'];?> <?php echo $v['lastname'];?></option>
							<?php endforeach; ?>
						</select>
                        <a href="#" class="pull-right resetMembers">Reset Members</a>
					</td>
				</tr>
				<tr>
					<td><small>Select Estimate Status</small>
						<select name="search_status" class="input-sm form-control" >
							<option value="">Select Status</option>
							<?php foreach($statuses as $k=>$v) : ?>
								<option <?php if (isset($search_status) && $search_status == $v->est_status_id): ?>selected="selected"<?php endif; ?> value="<?php echo $v->est_status_id;?>"><?php echo $v->est_status_name;?></option>
							<?php endforeach; ?>
						</select>

					</td>
				</tr>
				<tr>
					<td><small>Estimate Description</small>
						<textarea name="search_estimate_description" type="text" class="input-sm form-control" placeholder="Description has..."
						   value="<?php if (isset($search_desc)) : echo $search_desc;  endif;?>"></textarea>
					</td>
				</tr>
			</table>
		</div>
		<div class="text-center" style="padding: 6px 15px;">
			<button class="btn btn-sm btn-default" style="width:100%; background-color: #ededed;" type="submit" id="searchEst">Go!</button>
		</div>
	</form>
    <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
</div>
<script>
	$(document).ready(function () {
        $('.datepicker').datepicker({
            format: $('#php-variable').val(),
            clearBtn: true
        });
		$(document).on('click', '.dropdown-menu.animated.fadeInDown.searchFrom', function (e) {
			e.stopPropagation();
		});
      $(document).on('click', '.resetDate', function(){
        $('.datepicker').datepicker('setDate', null);
      });

      $(document).on('click', '.resetMembers', function(){
        $('.search_workers').val(null);
      });
	});
	/*setTimeout(function() {
		$('.sliderPrice').slider({
			min:0,
			max:10000,
			step:5,
			tooltip:'hide',
			value:[<?php echo isset($search_estimate_price_from) ? $search_estimate_price_from : 500; ?>, <?php echo isset($search_estimate_price_to) ? $search_estimate_price_to : 1500; ?>]
		}).on('slide', function(ev){
			$('input[name="search_estimate_price_from"]').val(ev.value[0]);
			$('input[name="search_estimate_price_to"]').val(ev.value[1]);
			console.log(ev);
			
		}).on('slideStop', function(ev){
			$('input[name="search_estimate_price_from"]').val(ev.value[0]);
			$('input[name="search_estimate_price_to"]').val(ev.value[1]);
		});
		$('.slider.slider-horizontal').css('width', '210px');
		$('.sliderServicePrice').slider({
			min:0,
			max:10000,
			step:5,
			tooltip:'hide',
			value:[<?php echo isset($search_service_price_from) ? $search_service_price_from : 500; ?>, <?php echo isset($search_estimate_price_to) ? $search_estimate_price_to : 1500; ?>]
		}).on('slide', function(ev){
			$('input[name="search_service_price_from"]').val(ev.value[0]);
			$('input[name="search_service_price_to"]').val(ev.value[1]);
			
			return false;
		});
		$('.slider.slider-horizontal').css('width', '210px');
	}, 3000);
	function updateInput(name, input, val){
		$('.' + name).value = val;
		if(input == 'from')
			value = [val, ]
		$('.' + name).slider({
			value:[]
		});
	}*/
</script>
