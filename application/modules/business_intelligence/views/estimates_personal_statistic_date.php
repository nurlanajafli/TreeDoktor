<?php if ($data_available == TRUE) { ?>

<section class="panel panel-default p-n d-flex " style="flex-direction: column">
    <header class="panel-heading">Daily and weekly KPI
        for <?php echo $estimator_meta->firstname . " " . $estimator_meta->lastname; ?></header>

    <!-- Getting the days -->
    <div class="form-inline m-l-xs m-t-xs m-b-xs">
        <div class="col-sm-12 col-lg-5 w-100 w-md-50">
            <label class="row w-100">
                <span class="col-sm-12 p-n">From:</span>
                <input type="text" class="form-control w-100"
                       value="<?php if (isset($from_date_val) && $from_date_val != "") {
                           echo getDateTimeWithDate($from_date_val, 'Y-m-d');
                       } ?>" placeholder="Pick a date" id="dp1" name="from_date">
            </label>
        </div>

        <div class="col-sm-12 col-lg-5 w-100 w-md-50">
            <label class="row w-100">
                <span class="col-sm-12 p-n">To:</span>
                <input type="text" class="form-control w-100"
                       value="<?php if (isset($to_date_val) && $to_date_val != "") {
                           echo getDateTimeWithDate($to_date_val, 'Y-m-d');
                       } ?>" placeholder="Pick a date" id="dp2" name="to_date">
            </label>
        </div>

        <div class="col-sm-12 col-lg-2 p-n" style="margin: 5px 0;">
            <button id="get_by_dates" class="btn btn-info">View</button>
        </div>
    </div>
    <!-- /Getting the days -->
    
    <!-- Display Data -->
    <?php if ($dates_available == FALSE){
        echo "**Please select Date above";
    }else {
    $this->load->view('kpi');
	} ?>
</section>
		<!-- /Display Data -->
<?php } ?>

<!--Datepicker: -->
<script>
	$(function () {
		window.prettyPrint && prettyPrint();
		$('#dp1').datepicker({
			format: $('#php-variable').val()
		});
		$('#dp2').datepicker({
			format: $('#php-variable').val()
		});
		$('#dp3').datepicker();
		$('#dp3').datepicker();
		$('#dpYears').datepicker();
		$('#dpMonths').datepicker();


		var startDate = new Date(2012, 1, 20);
		var endDate = new Date(2012, 1, 25);
		$('#dp4').datepicker()
			.on('changeDate', function (ev) {
				if (ev.date.valueOf() > endDate.valueOf()) {
					$('#alert').show().find('strong').text('The start date can not be greater then the end date');
				} else {
					$('#alert').hide();
					startDate = new Date(ev.date);
					$('#startDate').text($('#dp4').data('date'));
				}
				$('#dp4').datepicker('hide');
			});
		$('#dp5').datepicker()
			.on('changeDate', function (ev) {
				if (ev.date.valueOf() < startDate.valueOf()) {
					$('#alert').show().find('strong').text('The end date can not be less then the start date');
				} else {
					$('#alert').hide();
					endDate = new Date(ev.date);
					$('#endDate').text($('#dp5').data('date'));
				}
				$('#dp5').datepicker('hide');
			});

		// disabling dates
		var nowTemp = new Date();
		var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

		var checkin = $('#dpd1').datepicker({
			onRender: function (date) {
				return date.valueOf() < now.valueOf() ? 'disabled' : '';
			}
		}).on('changeDate',function (ev) {
			if (ev.date.valueOf() > checkout.date.valueOf()) {
				var newDate = new Date(ev.date)
				newDate.setDate(newDate.getDate() + 1);
				checkout.setValue(newDate);
			}
			checkin.hide();
			$('#dpd2')[0].focus();
		}).data('datepicker');
		var checkout = $('#dpd2').datepicker({
			onRender: function (date) {
				return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
			}
		}).on('changeDate',function (ev) {
			checkout.hide();
		}).data('datepicker');
	});
</script>
