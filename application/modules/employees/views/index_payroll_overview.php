<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('reports'); ?>">Reports</a></li>
		<li class="active">Payroll Overview</li>
	</ul>

	<section class="panel panel-default p-n">
		<header class="panel-heading">
			Payroll Overview Report

			<div class="pull-right" >
				<a href="<?php echo base_url('employees/payroll_overview/' . ($payroll_id - 1)); ?>" class="btn btn-sm btn-info">&lt;&lt; Previous Weeks</a>
				<a href="<?php echo base_url('employees/payroll_overview/'); ?>" class="btn btn-sm btn-default">Current Weeks</a>
				<a href="<?php echo base_url('employees/payroll_overview/' . ($payroll_id + 1)); ?>" class="btn btn-sm btn-info">Next Weeks &gt;&gt;</a>
			</div>

			<div class="h4 pull-right b-a p-5 bg-white" style="">
<!--				<strong>--><?php //echo $payroll->payroll_start_date; ?><!-- - --><?php //echo $payroll->payroll_end_date; ?><!--</strong>-->
                <?php echo getDateTimeWithDate($payroll->payroll_start_date, 'Y-m-d'); ?> - <?php echo getDateTimeWithDate($payroll->payroll_end_date, 'Y-m-d'); ?>
			</div>
			<div class="clear"></div>
		</header>
		<div class="row">
			<section class="col-md-12 text-center">

				

					<?php $this->load->view('payroll_overview_table');?>
					
					<div class="row m-b-md">
						<div class="col-md-12">
						<?php if(isAdmin()) : ?>
							<a href="<?php echo base_url('employees/payroll_overview/' . $payroll_id . '/1'); ?>" class="btn btn-default">
								Payroll Overview PDF
							</a>
							<a target="_blank" href="<?php echo base_url('employees/payroll_all_pdf/' . $payroll_id); ?>" class="btn btn-default">
								Payroll PDF
							</a>
							<?php if(is_bucket_file('uploads/payrolls_pdf/backup_payroll_' . $payroll_id . '.pdf')) : ?>
							<a target="_blank" href="<?php echo base_url('uploads/payrolls_pdf/backup_payroll_' . $payroll_id . '.pdf'); ?>" class="btn btn-default">
								Backup Payroll PDF
							</a>
							<?php endif;?>
                            <a target="_blank" href="<?php echo base_url('employees/payroll_overview/' . $payroll_id . '?payroll_overview_csv=1'); ?>" class="btn btn-success">
                                Payroll Overview CSV
                            </a>
						<?php endif;?>
							<a target="_blank" href="<?php echo base_url('employees/all_payroll_comission/' . $payroll_id); ?>" class="btn btn-default">
								Comission PDF
							</a>
						</div>
					</div>
			</section>
		</div>
	</section>
</section>

<?php $this->load->view('includes/footer'); ?>
