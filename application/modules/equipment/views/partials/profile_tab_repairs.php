<?php
/**
 * @var \application\modules\equipment\models\Equipment $eq
 */

use application\modules\equipment\models\EquipmentRepair;

?>
<style>
    #profile_tab_repairs .truncate {
        max-width: 110px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (min-width: 768px) {
        #profile_tab_repairs .panel-heading .buttons {
            text-align: right;
        }
    }

    #profile_tab_repairs .panel > .row {
        margin-left: initial;
        margin-right: initial;
    }

    #profile_tab_repairs .data-list tr {
        --rgb: 255, 255, 255;
        background: rgba(var(--rgb), .8);
        white-space: nowrap;
    }

    #profile_tab_repairs .data-list tr:hover {
        --rgb: 245, 245, 245;
        background: rgba(var(--rgb), 1);
    }

    #profile_tab_repairs .table > thead th.sortable {
        cursor: pointer;
    }

    #profile_tab_repairs .table > thead th.sortable i {
        float: right;
        margin-top: 2px;
    }

    #profile_tab_repairs .table > thead th {
        white-space: nowrap;
        border-right: 2px solid #f1f1f1;
    }


    #profile_tab_repairs .edit-modal-dialog {
        width: 95%;
        max-width: 1200px;
    }

    #profile_tab_repairs .edit-modal-dialog .line {
        margin-right: 0px;
    }

    #profile_tab_repairs .edit-modal-dialog .row {
        margin-bottom: 4px;
    }

    #profile_tab_repairs .edit-modal-dialog .row .field-col {
        padding-left: 2px;
        padding-right: 2px;
    }

    #profile_tab_repairs .edit-modal-dialog .modal-body {
        padding-bottom: 2px;
        padding-right: 2px;
    }

    #profile_tab_repairs .edit-modal-dialog .modal-body > .form-group > .col-sm-12 {
        padding-left: 0px;
    }

    #profile_tab_repairs .edit-modal-dialog .modal-body > .form-group {
        margin-bottom: 0px;
        margin-right: 0px;
    }

    #profile_tab_repairs .btn-file {
        width: 100%;
    }

    #profile_tab_repairs .btn-file .action-upload {
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

    #profile_tab_repairs .hidden-on-tab {
        display: none;
    }
</style>
<section id="profile_tab_repairs" class="col-sm-12 panel panel-default p-n"
         diez-app="EquipmentProfileTabRepairsApp"
         diez-src="equipment/components/repairs.js"
         diez-deferred="true"
         data-equipment-id="<?php echo $eq->eq_id; ?>">
    <header class="panel-heading row m-r-none p-0">
        <div class="col-sm-6">
            <div class="btn-group">
                <button type="button" class="action-refresh btn btn-sm btn-default" title="Refresh"><i
                            class="fa fa-refresh"></i></button>
                <button type="button" class="action-create btn btn-sm btn-default"
                        title="Create Repair Request on <?php echo $eq->eq_name; ?>"><i
                            class="fa fa-plus"></i></button>
            </div>
        </div>
        <div class="col-sm-4 m-t-xs">
            <!--<div class="input-group">
                <input id="filter" type="text" class="input-sm form-control" placeholder="Filter by Part Name">
                <span class="input-group-btn">
                        <button class="action-filter btn btn-sm btn-default" type="button">Go!</button>
                      </span>
            </div>-->
        </div>
        <div class="buttons col-sm-2 v-middle text-right">
        </div>
    </header>
    <div class="table-responsive">
        <!-- Data display -->
        <table class="table" id="tbl_search_result">
            <thead>
            <tr>
                <th class="sortable">#</th>
                <th class="sortable hidden-on-tab">Equipment</th>
                <th class="sortable" data-sort="repair_status_id">Status</th>
                <th class="sortable" data-sort="repair_type_id">Type</th>
                <th class="sortable" data-sort="repair_priority">Priority</th>
                <th class="sortable" data-sort="assigned_id">Assigned To</th>
                <th><?php echo ucfirst(DISTANCE_MEASUREMENT); ?>/hrs</th>
                <th class="sortable">Description</th>
                <th class="sortable" data-sort="repair_created_at">Created At</th>
                <th class="sortable" data-sort="repair_end_at">End At</th>
                <th class="sortable" data-sort="user_id">Added By</th>
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
    <div id="edit" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" class="form-horizontal">
                <div class="modal-content panel panel-default p-n">
                    <header class="panel-heading">Edit Repair</header>

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
<?php //$this->load->view_hb('profile_tab_repair_create'); /** Loaded in profile.php */ ?>
<?php $this->load->view_hb('repair_assign'); ?>
<?php $this->load->view_hb('includes/paginator'); ?>
<?php $this->load->view_hb('equipment_note_block'); ?>
<?php $this->load->view_hb('repair-notes'); ?>

<script>
    var isAdmin = <?php echo isAdmin() ? 'true' : 'false' ?>;
    var PAGE_NUM_URI_SEGMENT = 3;
    var repairPriorities = <?php echo json_encode(EquipmentRepair::PRIORITIES); ?>;
    var allTaxes = <?php echo json_encode(all_taxes()); ?>;
    var defaultTax = <?php echo json_encode(getDefaultTax()); ?>;
    var repairCompleteStatus = <?php echo json_encode($repairStatuses->where('repair_status_flag_completed',
        1)->first()); ?>;
</script>

    
