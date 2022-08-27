<!--Modal-->
<script src="<?php echo base_url('assets/vendors/notebook/js/combodate/combodate.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/libs/moment.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/select2/select2.min.js'); ?>"></script>

<div id="new_lead" class="modal fade" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n" style="overflow-x: hidden;">
            <?php echo form_open(base_url() . "leads/create_lead") ?>
            <?php echo $this->load->view('leads/new_lead_modal'); ?>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php if (isset($this->session->userdata["CC"]) && $this->session->userdata["CC"] == 0) : ?>
<?php else : ?>
	<div id="billing_details" data-client-id="<?php echo $client_data->client_id; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content panel panel-default p-n">
				<header class="panel-heading">Billing Details for&nbsp;<?php echo $client_data->client_name; ?>
					<a href="#card-form" data-placement="right" data-toggle="modal" class="btn btn-success btn-xs add_credit_card pull-right">
						<i class="fa fa-plus"></i>
					</a>
					<div class="clear"></div>
				</header>
				<div class="modal-body p-10">
                    <div class="cards-info"></div>
				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
<!--Content-->
<section class="panel panel-default p-n" style="overflow: unset">
	<!-- Client Files Header-->
	<header class="panel-heading p-n">
        <div class="pull-left">
            <?php $this->load->view('files/client_files_tabs'); ?>
        </div>
        <div class="pull-left" id="client_locations_filter_view_tpl">
            <?php $this->load->view('files/client_files_filters'); ?>
        </div>
        <div class="pull-right m-r-xs m-t-xs">
            <?php //if(isset($brands) && (!isset($estimate_data) && $this->uri->segment(1)!='estimates')): ?>
                <div class="pull-right m-bottom-0 m-right-5" style="margin-bottom: -14px; margin-top: -8px;">
                    <?php $this->load->view('brands/partials/client_brands_dropdown', ['brand_style'=>'', 'class'=>'input-sm select2 p-n']); ?>
                </div>
            <?php //endif; ?>
        </div>
		<div class="clearfix"></div>
	</header>
	<!-- Client Files Data -->
    <div id="client-files-block" class="tab-content">
        <div class="client-files-preloader">
            <img src="/assets/img/preloader.gif">
        </div>
    </div>

    <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
</section>

<?php if($total_confirmed_estimates_sum || $total_estimates_sum ): ?>
    <section class="panel panel-default p-n">
        <header class="panel-heading">
            <div class="pull-left">Totals</div>
            <div class="clearfix"></div>
        </header>
        <div class="table-responsive">
            <table class="table tableTasks">
                <tbody>
                <?php if($total_confirmed_estimates_sum): ?>
                    <tr>
                        <td>
                            <strong>Total For Confirmed Estimates</strong>
                        </td>
                        <td>
                            <strong><?php echo money($total_confirmed_estimates_sum['sum']); ?></strong>
                        </td>
                        <td></td>
                    </tr>
                <?php endif; ?>
                <?php if($total_estimates_sum): ?>
                    <tr>
                        <td>
                            <strong>Total For Estimates</strong>
                        </td>
                        <td>
                            <strong><?php echo money($total_estimates_sum['sum']); ?></strong>
                        </td>
                        <td></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<section class="panel panel-default p-n">
    <!-- Client Files Header-->
    <header class="panel-heading">
        <div class="pull-left">All client tasks</div>
        <div class="clearfix"></div>
    </header>
    <div class="table-responsive">
        <table class="table tableTasks">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Id</th>
                    <th width="100px">Lead No</th>
                    <th width="120px">Date Created</th>
                    <th>Status</th>
                    <th>Category</th>
                    <th>Task Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Tasks -->
                <?php $this->load->view('clients/partials/client_tasks'); ?>
            </tbody>
        </table>
        <!-- End Tasks -->
    </div>
</section>

<?php $this->load->view('partials/leads_preview_modal'); ?>
<?php $this->load->view('files/client_files_tpl'); ?>
