<?php
/**
 * @var \application\modules\equipment\models\Equipment $eq
 */

use application\modules\equipment\models\EquipmentService;
use application\modules\equipment\models\EquipmentServiceType;

?>
<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/vendors/kartik-v/fileinput/css/fileinput.css'); ?>"
      type="text/css"/>
<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/equipment/equipment.css'); ?>"
      type="text/css"/>
<style>
    #services .truncate {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (min-width: 768px) {
        #services .panel-heading .buttons {
            text-align: right;
        }
    }

    #services .panel > .row {
        margin-left: initial;
        margin-right: initial;
    }

    #services .data-list tr {
        /*--rgb: 255, 255, 255;*/
        /*background: rgba(var(--rgb), .8);*/
        white-space: nowrap;
    }

    #services .data-list tr:hover:not(.bg-danger):not(.bg-warning) {
        --rgb: 245, 245, 245;
        background: rgba(var(--rgb), 1);
    }

    #services .data-list tr.bg-danger:hover {
        background-color: #bf3039;
    }

    #services .data-list tr.bg-warning:hover {
        background-color: #ff9800;
    }

    #services .data-list tr.bg-danger .btn-danger {
        border-color: #fff;
    }

    #services .table > thead th.sortable {
        cursor: pointer;
    }

    #services .table > thead th.sortable i {
        float: right;
        margin-top: 2px;
    }

    #services .table > thead th {
        white-space: nowrap;
        border-right: 2px solid #f1f1f1;
    }

    #services {
        overflow-x: auto;
        overflow-y: auto;
        margin-top: 34px;
    }

    #services > header {
        padding-right: 0px;
        padding-left: 0px;
    }

    #services > header > .nav {
        display: inline-block;
        padding-left: 25px;
        padding-right: 25px;
    }

    #services > section {
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

    #services .panel-heading .select2-container {
        top: 4px;
    }
    #services .complete-modal-dialog,
    #services .edit-report-modal-dialog {
        width: 80%;
        max-width: 1000px;
    }

    #services .complete-modal-dialog .line,
    #services .edit-report-modal-dialog .line {
        margin-right: 0px;
    }

    #services .complete-modal-dialog .row,
    #services .edit-report-modal-dialog .row {
        margin-bottom: 4px;
    }

    #services .complete-modal-dialog .row .field-col,
    #services .edit-report-modal-dialog .row .field-col {
        padding-left: 2px;
        padding-right: 2px;
    }

    #services .complete-modal-dialog .modal-body,
    #services .edit-report-modal-dialog .modal-body {
        padding-bottom: 2px;
        padding-right: 2px;
    }

    #services .complete-modal-dialog .modal-body > .form-group > .col-sm-12,
    #services .edit-report-modal-dialog .modal-body > .form-group > .col-sm-12 {
        padding-left: 0px;
    }

    #services .complete-modal-dialog .modal-body > .form-group,
    #services .edit-report-modal-dialog .modal-body > .form-group {
        margin-bottom: 0px;
        margin-right: 0px;
    }

    #services .btn-file {
        width: 100%;
    }

    #services .btn-file .action-upload {
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

    .select2-container a {
        height: initial !important;
    }
</style>
<!-- All clients display -->
<ul class="breadcrumb no-border no-radius b-b b-light">
    <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
    <li class="active">Equipment Services</li>
</ul>
<section id="services" diez-app="EquipmentServicesApp" diez-src="equipment/components/services.js"
         class="scrollable p-sides-15">

    <section class="col-sm-12 panel panel-default p-n">
        <header class="panel-heading row m-r-none">
            <div class="col-sm-1">
                <div class="btn-group">
                    <button type="button" class="action-refresh btn btn-sm btn-default" title="Refresh"><i
                                class="fa fa-refresh"></i></button>
                    <button type="button" class="action-create btn btn-sm btn-default" title="Create Service"><i
                                class="fa fa-plus"></i></button>
                </div>
            </div>
            <!--            <div class="col-sm-4 m-t-xs">-->
            <!--                Upcoming Services-->
            <!--            </div>-->
            <div class="col-sm-11 v-middle form-inline">
                <!--                <div class="form-group p-right-15">-->
                <!--                    <label class="" for="">Service Type</label>-->
                <!--                    <select name="filter[service_type_form]" class="action-filter input-sm form-control input-s inline v-middle">-->
                <!--                        <option value="">All</option>-->
                <!--                        --><?php //foreach (EquipmentServiceType::FORMS as $key => $name): ?>
                <!--                            <option value="--><? //= $key ?><!--">--><? //= $name ?><!--</option>-->
                <!--                        --><?php //endforeach; ?>
                <!--                    </select>-->
                <!--                </div>-->
                <div class="form-group p-right-15">
                    <label class="" for="">Service Name</label>
                    <select name="filter[service_type_id]"
                            class="action-filter input-sm form-control input-s inline v-middle">
                        <option value="">All</option>
                        <?php foreach ($serviceTypes as $stype): ?>
                            <option value="<?= $stype->service_type_id ?>"><?= $stype->service_type_name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group p-right-15">
                    <label class="" for="eq_id">Equipment</label>
                    <input id="eq_id" name="filter[eq_id]" class="m-b select-two"
                           data-select-route="equipment" placeholder="Select Equipment">
                </div>
                <div class="form-group p-right-15">
                    <label class="" for="">Due</label>
                    <select name="filter[due]"
                            class="action-filter input-sm form-control input-s inline v-middle">
                        <option value="">This week + Overdue</option>
                        <option value="n-week">Next week</option>
                        <option value="month">This month + Overdue</option>
                        <option value="n-month">Next month</option>
                        <option value="overdue">Overdue</option>
                        <option value="all">All</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="button" class="action-pdf btn btn-sm btn-success" title="PDF">PDF</button>
                </div>
            </div>
        </header>
        <div class="table-responsive">
            <!-- Data display -->
            <table class="table" id="tbl_search_result">
                <thead>
                <tr>
                    <th class="sortable" data-sort="eq_id">Equipment</th>
                    <th class="sortable" data-sort="service_type_id">Service Type</th>
                    <th class="sortable" data-sort="service_name">Name</th>
                    <th class="sortable" data-sort="service_description">Description</th>
                    <th class="sortable" data-sort="service_next_date_due">Next due on</th>
                    <th class="sortable" data-sort="service_next_counter_due">Next
                        due <?php echo DISTANCE_MEASUREMENT; ?>/hrs
                    </th>
                    <th>Last done on</th>
                    <th>Last done <?php echo DISTANCE_MEASUREMENT; ?>/hrs</th>
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

<?php $this->load->view_hb('service_row'); ?>
<?php $this->load->view_hb('service_edit'); ?>
<?php $this->load->view_hb('service_report_edit'); ?>
<?php $this->load->view_hb('service_postpone'); ?>
<?php $this->load->view_hb('includes/paginator'); ?>
<?php $this->load->view_hb('form_multi_employee_row'); ?>
<?php $this->load->view_hb('form_multi_part_row'); ?>
<?php $this->load->view_hb('form_multi_file_row'); ?>

<script>
    var isAdmin = <?php echo isAdmin() ? 'true' : 'false' ?>;
    var PAGE_NUM_URI_SEGMENT = 3;
    var serviceDatePeriodTypes = <?php echo json_encode(EquipmentService::DATE_PERIOD_TYPES); ?>;
    var serviceTypeForms = <?php echo json_encode(EquipmentServiceType::FORMS); ?>;
    var currentUserData = <?php echo json_encode(request()->user()->load(['employee'])); ?>;
    var DATE_FORMAT = "<?php echo getJSDateFormat(); ?>";
</script>

<?php $this->load->view('includes/footer'); ?>

    
