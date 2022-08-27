<?php
/**
 * @var \application\modules\equipment\models\Equipment $eq
 */

use application\modules\equipment\models\EquipmentServiceReport;

?>
<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/vendors/kartik-v/fileinput/css/fileinput.css'); ?>"
      type="text/css"/>
<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/equipment/equipment.css'); ?>"
      type="text/css"/>
<style>
    #service_reports .truncate {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (min-width: 768px) {
        #service_reports .panel-heading .buttons {
            text-align: right;
        }
    }

    #service_reports .panel > .row {
        margin-left: initial;
        margin-right: initial;
    }

    #service_reports .data-list tr {
        --rgb: 255, 255, 255;
        background: rgba(var(--rgb), .8);
        white-space: nowrap;
    }

    #service_reports .data-list tr:hover {
        --rgb: 245, 245, 245;
        background: rgba(var(--rgb), 1);
    }

    #service_reports .table > thead th.sortable {
        cursor: pointer;
    }

    #service_reports .table > thead th.sortable i {
        float: right;
        margin-top: 2px;
    }

    #service_reports .table > thead th {
        white-space: nowrap;
        border-right: 2px solid #f1f1f1;
    }

    #service_reports {
        overflow-x: auto;
        overflow-y: auto;
        margin-top: 34px;
    }

    #service_reports > header {
        padding-right: 0px;
        padding-left: 0px;
    }

    #service_reports > header > .nav {
        display: inline-block;
        padding-left: 25px;
        padding-right: 25px;
    }

    #service_reports > section {
        display: inline-table;
    }

    .switch {
        top: -10px;
    }

    .switch span {
        top: 10px;
    }

    .select2-container {
        width: 100%;
    }

    .form-inline .select2-container {
        display: inline-block;
        width: auto;
        min-width: 200px;
        height: 35px !important;
    }

    #service_reports .edit-modal-dialog {
        width: 80%;
        max-width: 1000px;
    }

    #service_reports .edit-modal-dialog .line {
        margin-right: 0px;
    }

    #service_reports .edit-modal-dialog .row {
        margin-bottom: 4px;
    }

    #service_reports .edit-modal-dialog .row .field-col {
        padding-left: 2px;
        padding-right: 2px;
    }

    #service_reports .edit-modal-dialog .modal-body {
        padding-bottom: 2px;
        padding-right: 2px;
    }

    #service_reports .edit-modal-dialog .modal-body > .form-group > .col-sm-12 {
        padding-left: 0px;
    }

    #service_reports .edit-modal-dialog .modal-body > .form-group {
        margin-bottom: 0px;
        margin-right: 0px;
    }

    #service_reports .btn-file {
        width: 100%;
    }

    #service_reports .btn-file .action-upload {
        position: absolute;
        z-index: 2;
        top: 0;
        left: 0;
        filter: alpha(opacity=0);
        -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";
        opacity: 0;
        background-color: transparent;
        color: transparent;
        height: 100%;
    }
</style>
<!-- All clients display -->
<ul class="breadcrumb no-border no-radius b-b b-light">
    <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
    <li class="active">Equipment Service Reports</li>
</ul>
<section id="service_reports" diez-app="EquipmentServiceReportsApp" diez-src="equipment/components/service-reports.js"
         class="scrollable p-sides-15">
    <section class="col-sm-12 panel panel-default p-n">
        <header class="panel-heading row m-r-none">
            <div class="col-sm-1">
                <div class="btn-group">
                    <button type="button" class="action-refresh btn btn-sm btn-default" title="Refresh"><i
                                class="fa fa-refresh"></i></button>
                </div>
            </div>
            <div class="col-sm-11 v-middle form-inline">
                <div class="form-group p-right-15">
                    <label class="" for="">Report Type</label>
                    <select name="filter[service_report_type]"
                            class="action-filter input-sm form-control input-s inline v-middle">
                        <option value="">All</option>
                        <?php foreach (EquipmentServiceReport::TYPES as $key => $name): ?>
                            <option value="<?= $key ?>"><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group p-right-15">
                    <label class="" for="">Service Name</label>
                    <select name="filter[service_type_id]"
                            class="action-filter input-sm form-control input-s inline v-middle" style="width:200px">
                        <option value="">All</option>
                        <?php foreach ($serviceTypes as $stype): ?>
                            <option value="<?= $stype->service_type_id ?>"><?= $stype->service_type_name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group  p-right-15">
                    <label class="" for="eq_id">Equipment</label>
                    <input id="eq_id" name="filter[eq_id]" class="m-b select-two"
                           data-select-route="equipment" placeholder="Select Equipment">
                </div>
                <div class="form-group">
                    <label class="" for="user_id">Complete by</label>
                    <input id="user_id" name="filter[user_id]" class="m-b select-two"
                           data-select-route="users" placeholder="Select User">
                </div>
            </div>
        </header>
        <div class="table-responsive">
            <!-- Data display -->
            <table class="table" id="tbl_search_result">
                <thead>
                <tr>
                    <th class="sortable">#</th>
                    <th class="sortable" data-sort="eq_id">Equipment</th>
                    <th class="sortable" data-sort="service_id">Service Name</th>
                    <th class="sortable" data-sort="service_report_type">Report Type</th>
                    <th class="sortable" data-sort="service_report_note">Note</th>
                    <th><?php echo ucfirst(DISTANCE_MEASUREMENT); ?>/hrs</th>
                    <th class="sortable" data-sort="service_report_created_at">Created on</th>
                    <th class="sortable" data-sort="service_report_postponed_to">Postponed to</th>
                    <th class="sortable" data-sort="user_id">Completed by</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody class="data-list">
                </tbody>
            </table>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="paginator col-sm-5 text-right text-center-xs pull-right">
                </div>
            </div>
        </footer>
    </section>
    <div id="edit" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" class="form-horizontal">
                <div class="modal-content panel panel-default p-n">
                    <header class="panel-heading">
                        <span>Edit</span>
                        <button type="button" class="close" data-dismiss="modal">×</button>
                    </header>
                    <div class="modal-footer">
                        <a class="btn btn-warning download-pdf" target="_blank" style="display: none">Download PDF</a>
                        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                        <button class="btn btn-info" type="submit" style="30px"><span
                                    class="btntext">Save</span></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<?php $this->load->view_hb('service_report_row'); ?>
<?php $this->load->view_hb('service_report_edit'); ?>
<?php $this->load->view_hb('includes/paginator'); ?>
<?php $this->load->view_hb('form_multi_employee_row'); ?>
<?php $this->load->view_hb('form_multi_part_row'); ?>
<?php $this->load->view_hb('form_multi_file_row'); ?>


<script>
    var isAdmin = <?php echo isAdmin() ? 'true' : 'false' ?>;
    var PAGE_NUM_URI_SEGMENT = 3;
    var postponeReportType = <?php echo EquipmentServiceReport::TYPE_POSTPONED ?>;
    var allTaxes = <?php echo json_encode(all_taxes()); ?>;
    var defaultTax = <?php echo json_encode(getDefaultTax()); ?>;
    var currentUserData = <?php echo json_encode(request()->user()->load(['employee'])); ?>;
    var DATE_FORMAT = "<?php echo getJSDateFormat(); ?>";
</script>

<?php $this->load->view('includes/footer'); ?>

    
