<?php $this->load->view('includes/header'); ?>
<style>
    /* Canvas Chart Alignment */
    .flot-base {
        left: -25px !important;
    }

    .flot-text {
        left: -25px !important;
    }

    .flot-1ine-y-axis-text {
        display: inline;
        align-self: center;
        transform: rotate(270deg);
    }
    /* End Canvas Chart Alignment */
</style>


<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('estimates'); ?>">Estimates</a></li>
		<li class="active">Estimates</li>
	</ul>
	<section class="col-sm-12 panel panel-default p-n">
		<header class="panel-heading">Estimates</header>

		<div class="m-l m-b-sm">
			<div class="form-inline user-estimates-form">
				Estimators:
				<?php echo form_open(base_url() . "business_intelligence/estimates_report") ?>
				<?php    $options = array();

				if (isset($users) && !empty($users)) {
					foreach ($users as $user) {
						$options["$user->id"] = $user->firstname . " " . $user->lastname;
					}
				}

				$attr = 'class="form-control"';

				echo form_dropdown('user_id', $options, isset($estimator_id) ? $estimator_id : '', $attr); ?>
				
				<input type="submit" name="view" value="View" id="estimator_id" class="btn btn-info"/>

				<?php form_close(); ?>
			</div>
		</div>
	</section>



	<?php $this->load->view('business_intelligence/estimates_corp_statistic'); ?>
	<div class="clearfix"></div>
	<div class="row">
		<section class="col-sm-7" id="personal_statistic_section">
		</section>
		<section class="col-sm-5" id="personal_statistic_section_date">
		</section>
		<div class="clearfix"></div>
        <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
	</div>
	<section id="personal_files_section">
	</section>
	
	<?php //$this->load->view('business_intelligence/estimates_personal_statistic'); ?>
	<?php //$this->load->view('business_intelligence/estimates_personal_files'); ?>
	<section class="col-sm-12 panel panel-default p-n">
		<header class="panel-heading">Days from Estimate to Workorder</header>

        <div class="d-flex">
            <div class="flot-1ine-y-axis-text font-bold" >Workorders</div>

            <div class="w-100 p-left-0">
                <div class="panel-body p-left-0">
                    <div id="flot-1ine" style="height:400px"></div>
                </div>
            </div>
        </div>

        <div class="text-center font-bold p-bottom-5">Days</div>
	</section>
</section>

<script src="<?php echo base_url();?>assets/vendors/notebook/js/charts/flot/jquery.flot.min.js"></script>
  <script src="<?php echo base_url();?>assets/vendors/notebook/js/charts/flot/jquery.flot.tooltip.min.js"></script>
  <script src="<?php echo base_url();?>assets/vendors/notebook/js/charts/flot/jquery.flot.resize.js"></script>
  <script src="<?php echo base_url();?>assets/vendors/notebook/js/charts/flot/jquery.flot.grow.js"></script>
<script>

	var prev_selected_user;
	var d1 = [];
	<?php foreach($est_wo_period as $key=>$val) : ?>
		<?php if($val['count_days'] >= 0 && $val['count_days'] <= 100) : ?>
			d1.push([<?php echo $val['count_days']; ?>, <?php echo $val['count']; ?>]);
		<?php endif; ?>
	<?php endforeach; ?>
  $("#flot-1ine").length && $.plot($("#flot-1ine"), [{
          data: d1
      }], 
      {
        series: {
            lines: {
                show: true,
                lineWidth: 2,
                fill: true,
                fillColor: {
                    colors: [{
                        opacity: 0.0
                    }, {
                        opacity: 0.2
                    }]
                }
            },
            points: {
                radius: 5,
                show: true
            },
            grow: {
              active: true,
              steps: 50
            },
            shadowSize: 2
        },
        grid: {
            hoverable: true,
            clickable: true,
            tickColor: "#f0f0f0",
            borderWidth: 1,
            color: '#f0f0f0'
        },
        colors: ["#65bd77"],
        xaxis:{
			ticks: 30
        },
        yaxis: {
            ticks: 20
        },
        tooltip: true,
        tooltipOpts: {
          content: "%x.1 day(s) - %y.4 workorder(s)",
          defaultTheme: false,
          shifts: {
            x: 0,
            y: 20
          }
        }
      }
  );
	
	$(document).ready(function(){
	
		$('.user-estimates-form form').submit(function(e){
			e.preventDefault();
			if($('.user-estimates-form select[name="user_id"]').val() != prev_selected_user) {
				$('#dp1').val('');
				$('#dp2').val('');
			}
			prev_selected_user = $('.user-estimates-form select[name="user_id"]').val();
			$.post(baseUrl + 'business_intelligence/ajax_estimates_report_users', {user_id : prev_selected_user,
						from_date : $('#dp1').val(), to_date : $('#dp2').val()}, function (resp) {
				if (resp.status)
				{
					if(resp.data.by_dates == 0){ 
						$('#personal_statistic_section').html(resp.data.stats_html);
						$('#personal_files_section').html(resp.data.files_html);						
					} 
					$('#personal_statistic_section_date').html(resp.data.stats_html_date);
					
				} else {
					errorMessage(resp.message);
				}
				return false;
			}, 'json');
		});	
		
		$('body').on('click', '#get_by_dates', function(e){
			e.preventDefault();
			$('.user-estimates-form form').submit();
		});
		
	});
</script>
<?php $this->load->view('includes/footer'); ?>
