<button class="btn btn-warning dropdown-toggle"  data-toggle="dropdown" >
	<i class="fa fa-filter"></i>
	<span class="caret"  style="margin-left:5px;"></span>
</button>
<div class="dropdown-menu animated fadeInDown searchFrom" style="min-width: 330px;">
	<span class="arrow top" style="left: 85%;"></span>

	<form name="search" id="client-search-form">
		<table class="table m-n">
            <tr>
                <td class="est-date-form__no-border">
                    <input name="search_tags" class="input-sm w-100  js-tags-select2" style="padding: 0px;" value="<?= isset($search_tags) ? $search_tags : '' ?>">
                </td>
            </tr>

            <tr>
                <td class="est-date-form__no-border">
                    <input name="search_keyword" id="search_tags" type="text" class="input-sm form-control"
                           placeholder="Name, Phone number, address..." value="" style="width: 300px">
                </td>
            </tr>
            <tr><td class="est-date-form__headline">ESTIMATES</td></tr>
			<tr>
				<td class="est-date-form est-date-form__no-border">
                    <div class="est-date-form__inputs">
                        <label class="pull-left" style=""><small>Select Date From</small>
                            <input name="search_estimate_from" class="datepicker form-control input-sm" type="text" placeholder="Date From" readonly value="">
                        </label>

                        <label class="pull-right" style=""><small>Select Date To</small>
                            <input name="search_estimate_to" class="datepicker form-control input-sm" type="text" placeholder="Date To" readonly value="">
                        </label>
                    </div>
                    <div class="est-date-form__reset">
                        <a href="#" class="pull-right resetDate">Reset Date</a>
                    </div>
				</td>
			</tr>
			<tr>
				<td>
					<div class="control-group">
						<label class="pull-left checkbox">
							<input name="estimate_confirm_status" type="checkbox">
							<small class="control-group__btn-checkbox">Search Only Confirmed Estimates</small>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<td class="est-price-form">
                    <label class="pull-left" style=""><small>Total From</small>
                        <input  style="" name="search_estimate_price_from" type="number" step="any" class="input-sm form-control" placeholder="Estimate Price From" value="">
                    </label>
                    <label class="pull-right" style=""><small>Total To</small>
                        <input  style="" name="search_estimate_price_to" type="number" step="any" class="input-sm form-control" placeholder="Estimate Price To" value="">
                    </label>

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
		</table>
		<div class="text-center" style="padding: 6px 15px;">
			<button class="btn btn-sm btn-default" style="width:100%; background-color: #ededed;" type="submit" id="searchEst">Search</button>
		</div>
	</form>
    <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
</div>
<script>
	$(document).ready(function () {
        let data = <?= isset($select2Tags)?$select2Tags:json_encode([]); ?>;
        Common.init_select2([
            {
                'selector':'.js-tags-select2',
                options:{
                    'data': data,
                    'tags': true,
                    'placeholder': 'Tags',
                    'separator': '|'
                }
            }
        ]);

        window.clientFilter = {};
        $('#client-search-form, #client-search-name-form').on('submit', function(e) {
            e.preventDefault();

            let sendData = new FormData(e.target);
            window.clientFilter = Object.fromEntries(sendData);

            ClientsList.dataTable().ajax.reload();
        })

        $('.datepicker').datepicker({format: $('#php-variable').val()});
		$(document).on('click', '.dropdown-menu.animated.fadeInDown.searchFrom', function (e) {
			e.stopPropagation();
		});
		$(document).on('click', '.resetDate', function(){
			$('.datepicker').datepicker('clearDates');
		});
	});

</script>
