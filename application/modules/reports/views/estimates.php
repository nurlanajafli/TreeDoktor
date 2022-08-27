<?php $this->load->view('includes/header'); ?>

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('reports'); ?>">Reports</a></li>
		<li class="active">Estimates</li>
	</ul>
	<section class="col-sm-12 panel panel-default p-n">
		<header class="panel-heading">Estimates</header>

		<div class="m-l m-b-sm">
			<div class="form-inline">
				Estimators:
				<?php echo form_open(base_url() . "reports/estimates") ?>
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



	<?php $this->load->view('reports/estimates_corp_statistic'); ?>
	<?php $this->load->view('reports/estimates_personal_statistic'); ?>
	<?php $this->load->view('reports/estimates_personal_files'); ?>
	<section class="col-sm-12 panel panel-default p-n">
		<header class="panel-heading">Days from Estimate to Workorder</header>
		<div class="panel-body">
			<div id="flot-1ine" style="height:400px"></div>
		</div>
	</section>
</section>

<script src="<?php echo base_url();?>assets/vendors/notebook/js/charts/flot/jquery.flot.min.js"></script>
  <script src="<?php echo base_url();?>assets/vendors/notebook/js/charts/flot/jquery.flot.tooltip.min.js"></script>
  <script src="<?php echo base_url();?>assets/vendors/notebook/js/charts/flot/jquery.flot.resize.js"></script>
  <script src="<?php echo base_url();?>assets/vendors/notebook/js/charts/flot/jquery.flot.grow.js"></script>
<script>

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
</script>
<?php $this->load->view('includes/footer'); ?>
