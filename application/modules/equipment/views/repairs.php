<?php
/**
 * @var \application\modules\equipment\models\Equipment $eq
 */

use application\modules\equipment\models\EquipmentRepair;

?>
<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/vendors/kartik-v/fileinput/css/fileinput.css'); ?>"
      type="text/css"/>
<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/equipment/equipment.css'); ?>"
      type="text/css"/>
<style>
    #repairs .truncate {
        max-width: 110px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (min-width: 768px) {
        #repairs .panel-heading .buttons {
            text-align: right;
        }
    }

    #repairs .panel > .row {
        margin-left: initial;
        margin-right: initial;
    }

    #repairs .data-list tr {
        --rgb: 255, 255, 255;
        background: rgba(var(--rgb), .8);
        white-space: nowrap;
    }

    #repairs .data-list tr:hover {
        --rgb: 245, 245, 245;
        background: rgba(var(--rgb), 1);
    }

    #repairs .table > thead th.sortable {
        cursor: pointer;
    }

    #repairs .table > thead th.sortable i {
        float: right;
        margin-top: 2px;
    }

    #repairs .table > thead th {
        white-space: nowrap;
        border-right: 2px solid #f1f1f1;
    }

    #repairs {
        overflow-x: auto;
        overflow-y: auto;
        margin-top: 34px;
    }

    #repairs > header {
        padding-right: 0px;
        padding-left: 0px;
    }

    #repairs > header > .nav {
        display: inline-block;
        padding-left: 25px;
        padding-right: 25px;
    }

    #repairs > section {
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

    #repairs .edit-modal-dialog {
        width: 95%;
        max-width: 1200px;
    }

    #repairs .edit-modal-dialog .line {
        margin-right: 0px;
    }

    #repairs .edit-modal-dialog .row {
        margin-bottom: 4px;
    }

    #repairs .edit-modal-dialog .row .field-col {
        padding-left: 2px;
        padding-right: 2px;
    }

    #repairs .edit-modal-dialog .modal-body {
        padding-bottom: 2px;
        padding-right: 2px;
    }

    #repairs .edit-modal-dialog .modal-body > .form-group > .col-sm-12 {
        padding-left: 0px;
    }

    #repairs .edit-modal-dialog .modal-body > .form-group {
        margin-bottom: 0px;
        margin-right: 0px;
    }

    #repairs .btn-file {
        width: 100%;
    }

    #repairs .btn-file .action-upload {
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
    <li class="active">Equipment Repair Requests</li>
</ul>
<section id="repairs" diez-app="EquipmentRepairsApp" diez-src="equipment/components/repairs.js"
         class="scrollable p-sides-15">

    <section class="col-sm-12 panel panel-default p-n">
        <header class="panel-heading row m-r-none">
            <div class="col-sm-1">
                <div class="btn-group">
                    <button type="button" class="action-refresh btn btn-sm btn-default" title="Refresh"><i
                                class="fa fa-refresh"></i></button>
                    <button type="button" class="action-create btn btn-sm btn-default" title="Create Repair Request"><i
                                class="fa fa-plus"></i></button>
                </div>
            </div>
            <div class="col-sm-11 v-middle form-inline">
                <div class="form-group p-right-15">
                    <label class="" for="">Status</label>
                    <select name="filter[repair_status_id]"
                            class="action-filter input-sm form-control input-s inline v-middle">
                        <option value="">All</option>
                        <?php foreach ($repairStatuses as $status): ?>
                            <option value="<?= $status->repair_status_id ?>"><?= $status->repair_status_name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group p-right-15">
                    <label class="" for="">Type</label>
                    <select name="filter[repair_type_id]"
                            class="action-filter input-sm form-control input-s inline v-middle">
                        <option value="">All</option>
                        <?php foreach ($repairTypes as $type): ?>
                            <option value="<?= $type->repair_type_id ?>"><?= $type->repair_type_name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group p-right-15">
                    <label class="" for="">Priority</label>
                    <select name="filter[repair_priority]"
                            class="action-filter input-sm form-control input-s inline v-middle">
                        <option value="">All</option>
                        <?php foreach (EquipmentRepair::PRIORITIES as $key => $name): ?>
                            <option value="<?= $key ?>"><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group p-right-15">
                    <label class="" for="assigned_id">Assigned to</label>
                    <input id="assigned_id" name="filter[assigned_id]" class="m-b select-two"
                           data-select-route="filterAssigned" placeholder="Select User">
                </div>
                <div class="form-group">
                    <label class="" for="eq_id">Equipment</label>
                    <input id="eq_id" name="filter[eq_id]" class="m-b select-two"
                           data-select-route="equipment" placeholder="Select Equipment">
                </div>
            </div>
        </header>
        <div class="table-responsive">
            <!-- Data display -->
            <table class="table" id="tbl_search_result">
                <thead>
                <tr>
                    <th class="sortable">#</th>
                    <th class="sortable">Equipment</th>
                    <th class="sortable" data-sort="repair_status_id">Status</th>
                    <th class="sortable" data-sort="repair_type_id">Type</th>
                    <th class="sortable" data-sort="repair_priority">Priority</th>
                    <th class="sortable" data-sort="assigned_id">Assigned to</th>
                    <th><?php echo ucfirst(DISTANCE_MEASUREMENT); ?>/hrs</th>
                    <th class="sortable">Description</th>
                    <th class="sortable" data-sort="repair_created_at">Created on</th>
                    <th class="sortable" data-sort="repair_end_at">Completed on</th>
                    <th class="sortable" data-sort="user_id">Completed by</th>
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
                    <header class="panel-heading">Edit Repair Request</header>
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

<?php $this->load->view_hb('repair_row'); ?>
<?php $this->load->view_hb('repair_edit'); ?>
<?php $this->load->view_hb('repair_create'); ?>
<?php $this->load->view_hb('repair_assign'); ?>
<?php $this->load->view_hb('includes/paginator'); ?>
<?php $this->load->view_hb('form_multi_employee_row'); ?>
<?php $this->load->view_hb('form_multi_part_row'); ?>
<?php $this->load->view_hb('form_multi_file_row'); ?>
<?php $this->load->view_hb('equipment_note_block'); ?>
<?php $this->load->view_hb('repair-notes'); ?>

<script>
    var isAdmin = <?php echo isAdmin() ? 'true' : 'false' ?>;
    var PAGE_NUM_URI_SEGMENT = 3;
    var repairPriorities = <?php echo json_encode(EquipmentRepair::PRIORITIES); ?>;
    var allTaxes = <?php echo json_encode(all_taxes()); ?>;
    var defaultTax = <?php echo json_encode(getDefaultTax()); ?>;
    var currentUserData = <?php echo json_encode(request()->user()->load(['employee'])); ?>;
    var DATE_FORMAT = "<?php echo getJSDateFormat(); ?>";
    var repairCompleteStatus = <?php echo json_encode($repairStatuses->where('repair_status_flag_completed',
        1)->first()); ?>;
</script>

<?php $this->load->view('includes/footer'); ?>

    
