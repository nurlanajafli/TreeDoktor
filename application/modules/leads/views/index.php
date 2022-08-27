<?php $this->load->view('includes/header'); ?>
<link href="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/css/bootstrap-editable.css'); ?>" rel="stylesheet">
<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/select2/theme.css'); ?>" type="text/css" />
<link rel="stylesheet" href="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.css'); ?>">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap.min.css">

<style>
    #my_trees-table_info {
        padding-bottom: 15px;
    }
    .select2-container {
        display: flex;
        height: unset !important;
    }
    table.dataTable{ margin-top: -1px!important; min-width: 100%; }
</style>

<!-- Assigned Leads -->
<section class="scrollable p-sides-10">
    <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
        <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
        <li class="active">Leads</li>
    </ul>
    <section id="my_leads_section" class="panel panel-default" style="min-height: 60px; display: none;">
        <header class="panel-heading" style="height: 40px;">
            <div class="row">
                <div class="col-md-3 col-sm-3 hidden-xs">
                    Leads assigned to me
                </div>

                <div class="pull-right" style="margin-top: -6px; margin-right: 10px;">
                    <a href="<?php echo base_url('leads/map'); ?>" role="button" class="btn btn-xs btn-dark pull-right" style="margin-top: -1px"
                       target="_blank"><i class="fa fa-map-marker"></i></a>
                </div>
            </div>
        </header>

        <div id="my_trees-table-container">
            <table id="my_trees-table" class="table table-striped b-t">
                <thead>
                <tr>
                    <th>ID</th>
                    <th style="min-width: 30px;">Client Name</th>
                    <th style="min-width: 30px;">Lead Date</th>
                    <th style="min-width: 30px;">Address</th>
                    <th style="min-width: 30px;">Created By</th>
                    <th style="min-width: 30px;">Assigned To</th>
                    <th style="min-width: 30px;">Assigned Date</th>
                    <th style="min-width: 10px;">Current Status</th>
                    <th style="min-width: 20px;">Postpone</th>
                    <th style="min-width: 10px;">Priority</th>
                    <th style="min-width: 15px;">Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </section>

    <section id="leads_section" class="panel panel-default" style="min-height: 60px; display: none;">
        <header class="panel-heading" style="height: 40px;">
            <div class="row">
                <div class="col-md-3 col-sm-3 hidden-xs">
                    Leads
                </div>

                <div class="pull-right" style="margin-top: -6px; margin-right: 10px;">
                    <a href="<?php echo base_url('leads/map'); ?>" role="button" class="btn btn-xs btn-dark pull-right" style="margin-top: -1px"
                       target="_blank"><i class="fa fa-map-marker"></i></a>
                </div>
            </div>
        </header>

        <div id="trees-table-container">
            <table id="trees-table" class="table table-striped b-t">
                <thead>
                <tr>
                    <th>ID</th>
                    <th style="min-width: 31px;">Client Name</th>
                    <th style="min-width: 31px;">Lead Date</th>
                    <th style="min-width: 31px;">Address</th>
                    <th style="min-width: 31px;">Created By</th>
                    <th style="min-width: 31px;">Assigned To</th>
                    <th style="min-width: 31px;">Assigned Date</th>
                    <th style="min-width: 31px;">Current Status</th>
                    <th style="min-width: 31px;">Postpone</th>
                    <th style="min-width: 31px;">Priority</th>
                    <th style="min-width: 31px;">Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </section>
</section>

<!-- Assigned Leads -->

<?php $this->load->view('clients/partials/leads_preview_modal'); ?>
<?php $this->load->view('clients/appointment/appointment_ajax_forms'); ?>

<script>
    const itemsForSelect2 = <?php echo getCategoriesItemsForSelect2() ?: 'null'; ?>;
    const tagsExpandLimit = `<?php echo $tagsExpandLimit ?? 30; ?>`;
</script>

<script src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/modules/clients/clients.js?v=<?php echo config_item('js_clients'); ?>"></script>
<script src="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.js'); ?>"></script>
<script src="<?php echo base_url(); ?>assets/js/modules/leads/leads.js?v=1.21"></script>

<?php $this->load->view('includes/footer'); ?>
