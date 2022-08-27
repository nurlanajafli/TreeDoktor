<?php
/**
 * @var \application\modules\equipment\models\Equipment $eq
 */

use application\modules\equipment\models\EquipmentService;
use application\modules\equipment\models\EquipmentServiceType;

?>
<style>
    #profile_tab_services .truncate {
        max-width: 110px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (min-width: 768px) {
        #profile_tab_services .panel-heading .buttons {
            text-align: right;
        }
    }

    #profile_tab_services .panel > .row {
        margin-left: initial;
        margin-right: initial;
    }

    #profile_tab_services .data-list tr {
        /*--rgb: 255, 255, 255;*/
        /*background: rgba(var(--rgb), .8);*/
        white-space: nowrap;
    }

    #profile_tab_services .data-list tr:hover:not(.bg-danger):not(.bg-warning) {
        --rgb: 245, 245, 245;
        background: rgba(var(--rgb), 1);
    }

    #profile_tab_services .data-list tr.bg-danger:hover {
        background-color: #bf3039;
    }

    #profile_tab_services .data-list tr.bg-warning:hover {
        background-color: #ff9800;
    }

    #profile_tab_services .data-list tr.bg-danger .btn-danger {
        border-color: #fff;
    }

    #profile_tab_services .table > thead th.sortable {
        cursor: pointer;
    }

    #profile_tab_services .table > thead th.sortable i {
        float: right;
        margin-top: 2px;
    }

    #profile_tab_services .table > thead th {
        white-space: nowrap;
        border-right: 2px solid #f1f1f1;
    }

    /*#profile_tab_services .select2-drop {*/
    /*    width: auto !important;*/
    /*    max-width: 300px !important;*/
    /*}*/

    #profile_tab_services .complete-modal-dialog,
    #profile_tab_services .edit-report-modal-dialog {
        width: 80%;
        max-width: 1000px;
    }

    #profile_tab_services .complete-modal-dialog .line,
    #profile_tab_services .edit-report-modal-dialog .line {
        margin-right: 0px;
    }

    #profile_tab_services .complete-modal-dialog .row,
    #profile_tab_services .edit-report-modal-dialog .row {
        margin-bottom: 4px;
    }

    #profile_tab_services .complete-modal-dialog .row .field-col,
    #profile_tab_services .edit-report-modal-dialog .row .field-col {
        padding-left: 2px;
        padding-right: 2px;
    }

    #profile_tab_services .complete-modal-dialog .modal-body,
    #profile_tab_services .edit-report-modal-dialog .modal-body {
        padding-bottom: 2px;
        padding-right: 2px;
    }

    #profile_tab_services .complete-modal-dialog .modal-body > .form-group > .col-sm-12,
    #profile_tab_services .edit-report-modal-dialog .modal-body > .form-group > .col-sm-12 {
        padding-left: 0px;
    }

    #profile_tab_services .complete-modal-dialog .modal-body > .form-group,
    #profile_tab_services .edit-report-modal-dialog .modal-body > .form-group {
        margin-bottom: 0px;
        margin-right: 0px;
    }

    #profile_tab_services .btn-file {
        width: 100%;
    }

    #profile_tab_services .btn-file .action-upload {
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

    #profile_tab_services .hidden-on-tab {
        display: none;
    }
</style>
<link rel="stylesheet" href="">
<section id="profile_tab_services" class="col-sm-12 panel panel-default p-n" diez-app="EquipmentProfileTabServicesApp"
         diez-src="equipment/components/services.js"
         diez-deferred="true"
         data-equipment-id="<?php echo $eq->eq_id; ?>"
         data-no-paginate="true"
         data-due="all">
    <header class="panel-heading row m-r-none">
        <div class="col-sm-6">
            <div class="btn-group">
                <button type="button" class="action-refresh btn btn-sm btn-default" title="Refresh"><i
                            class="fa fa-refresh"></i></button>
                <button type="button" class="action-create btn btn-sm btn-default"
                        title="Create Service on <?php echo $eq->eq_name; ?>"><i
                            class="fa fa-plus"></i></button>
            </div>
        </div>
        <div class="col-sm-4 m-t-xs">
            Upcoming Services
        </div>
        <div class="buttons col-sm-2 v-middle text-right">
        </div>
    </header>
    <div class="table-responsive">
        <!-- Data display -->
        <table class="table" id="tbl_search_result">
            <thead>
            <tr>
                <th class="sortable hidden-on-tab" data-sort="eq_id">Equipment</th>
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
    <div id="edit" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" class="form-horizontal">
                <div class="modal-content panel panel-default p-n">
                    <header class="panel-heading">Edit Service</header>
                    <div class="modal-footer">
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
<?php $this->load->view_hb('service_complete'); ?>
<?php $this->load->view_hb('service_postpone'); ?>

<script>
    var isAdmin = <?php echo isAdmin() ? 'true' : 'false' ?>;
    var PAGE_NUM_URI_SEGMENT = 3;
    var serviceDatePeriodTypes = <?php echo json_encode(EquipmentService::DATE_PERIOD_TYPES); ?>;
    var serviceTypeForms = <?php echo json_encode(EquipmentServiceType::FORMS); ?>;
</script>

    
