<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url(); ?>assets/js/modules/clients/clients_cc_form.js?v=1.01"></script>
<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/workorders/workorders.css'); ?>?v=1.1">

<!--Modals load-->
<?php $this->load->view('workorders/profile_modal_status'); ?>
<?php $this->load->view('clients/client_information_update_modal'); ?>
<!--/Modals load -->
<script async src="<?php echo base_url('/assets/js/modules/workorders/workorders_damages_modal.js'); ?>"></script>
<script>
    const CLIENT_NOTES = true;
    const NOTES_DATA = {
        client_id: <?php echo $estimate_data->client_id; ?>,
        lead_id: <?php echo $estimate_data->lead_id ?: null; ?>,
        client_only: false
    };

    $(document).ready(function () {
        $('.actionsList').on('click', function (event) {
            console.log('actionsList');
            $(this).parent().toggleClass('open');
        });
        $('body').on('click', function (e) {
            if (!$('.actionsDropdown').is(e.target)
                && $('.actionsDropdown').has(e.target).length === 0
                && $('.open').has(e.target).length === 0
                && !$(e.target).is('.modal.fade')
                && !$(e.target).parents('.modal.fade').length
            ) {
                $('.actionsDropdown').removeClass('open');
                $('.actionsDropdown').parent().removeClass('open');
            }
        });
    });
</script>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('workorders'); ?>">Workorders</a></li>
		<li class="active">Profile - <?php echo $workorder_data->workorder_no; ?></li>
		<a href="#" class="btn btn-default btn-xs pull-right dk actionsList" style="margin-top: -3px;">Actions <span class="caret"></span></a>
        <section class="dropdown-menu aside-xl actionsDropdown" style="right: 0; left: auto;">
            <section class="panel bg-white">

                <?php $this->load->view('profile_actions_dropdown'); ?>

            </section>
        </section>
	</ul>
	<!-- Client information -->
	<?php $this->load->view('clients/client_information_display'); ?>
	<!-- /Client information ends -->



	<section class="media m-n">
		<?php $this->load->view('workorders/profile_workorder_options'); ?>

		<section class="media-body">
			<?php //if(!isset($schedule_event)) : ?>
				<!-- Workorder Details -->
				<?php $this->load->view('workorders/profile_workorder_details'); ?>				
				<!-- /Workorder Details-->

			<?php $this->load->view('clients/client_information_payment_modal'); ?>
			<?php //endif; ?>
			<!-- Estimate data -->

			<?php $this->load->view('estimates/estimate_data_display'); ?>

			<!-- /Estimate data -->

			<!-- Workorder Projects -->

			<?php $this->load->view('workorders/profile_workorder_events'); ?>

			<!-- /Workorder Projects -->
		</section>
	</section>

	<!-- Estimate Project Requirements -->
	<?php $this->load->view('estimates/estimate_project_requirements'); ?>
	<!-- /Estimate Project Requirements -->

	<?php $this->load->view('clients/client_notes_form'); ?>

    <section class="panel panel-default p-n">
        <div id="client-notes"></div>
    </section>
    <?php $this->load->view('clients/notes/notes_tmp'); ?>

	<?php if ($this->session->userdata['user_type'] == 'admin') : ?>
		<?php $this->load->view('clients/client_information_tracking'); ?>
	<?php endif; ?>

	<!-- Footer -->
    <?php $this->load->view('clients/letters/client_letters_modal'); ?>

	<?php $this->load->view('workorders/profile_scripting'); ?>
	<?php $this->load->view('workorders/modals/workorders_damages_modal'); ?>
    <div id="card-block"></div>

	<!-- /Footer -->
</section>
<script src="<?php echo base_url(); ?>assets/js/modules/workorders/workorders.js?v=<?php echo config_item('js_workorders'); ?>"></script>

<script type="text/javascript">
	$('#eventInfo-report-modal').on('show.bs.modal', function (e) {
		$.post('<?php echo base_url('/events/get_event_report'); ?>', {id: e.relatedTarget.dataset.id, date: e.relatedTarget.dataset.date, team_id: e.relatedTarget.dataset.team_id, wo_id:e.relatedTarget.dataset.wo_id}, function(response){
			$('#eventInfo-report-modal .modal-body').html(response.html);
			init_report_editable();
		}, "JSON");
	});

	
	function init_report_editable(){
		$('.editable-report').editable({
			format: 'hh:mm a',
        	viewformat: 'hh:mm a',
        	template: "hh : mm a",
        	escape:true,
        	combodate:{
        		minYear: <?php echo (int)date("Y")-10; ?>,
    			maxYear: <?php echo (int)date("Y")+10; ?>,	
        	},
        	success: function(response, newValue) {
        		var data = JSON.parse(response);
        		if(data.container!=undefined)
                    $(data.container).html(data.html);
                else
		            $('.reports-container').html(data.html);

		        init_report_editable();
		    }
        });
	}
</script>
<!-- Footer -->
<?php $this->load->view('includes/footer'); ?>
<!-- /Footer -->








