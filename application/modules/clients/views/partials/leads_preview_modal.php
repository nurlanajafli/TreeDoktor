<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/leads/leads_preview_modal.css?v=1.00'); ?>" type="text/css" />

<form id="get-edit-form" data-global="false" data-type="ajax" data-url="<?php echo base_url('/leads/edit_form'); ?>" data-callback="Leads.set_edit_modal">
    <input type="hidden" name="id" value="">
</form>

<div id="lead-details-modal" class="modal fade overflow editLeadModal" tabindex="-1"  role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading"></header>
            <?php echo form_open('leads/update_lead', ['class' => 'editLeadForm']); ?>
            <input type="hidden" name="from_leads" value="1">
            
            <div class="modal-body">
                <div class="text-center">
                <img src="<?php echo base_url('/assets/img/preloader.gif'); ?>">
                </div>
            </div>
            <div class="modal-footer">                      
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                <?php echo form_submit('submit', 'Update', "class='btn btn-success'"); ?>
            </div>
            <?php echo form_close() ?>
        </div>
    </div>
</div>

<?php $this->load->view('clients/appointment/appointment_ajax_forms'); ?>
<?php $this->load->view('clients/appointment/schedule_appointment_modal'); ?>
