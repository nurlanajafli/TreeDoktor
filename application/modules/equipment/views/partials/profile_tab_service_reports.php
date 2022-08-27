<?php
/**
 * @var \application\modules\equipment\models\Equipment $eq
 */

use application\modules\equipment\models\EquipmentServiceReport;

?>
<style>
    #profile_tab_service_reports .truncate {
        max-width: 110px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (min-width: 768px) {
        #profile_tab_service_reports .panel-heading .buttons {
            text-align: right;
        }
    }

    #profile_tab_service_reports .panel > .row {
        margin-left: initial;
        margin-right: initial;
    }

    #profile_tab_service_reports .data-list tr {
        --rgb: 255, 255, 255;
        background: rgba(var(--rgb), .8);
        white-space: nowrap;
    }

    #profile_tab_service_reports .data-list tr:hover {
        --rgb: 245, 245, 245;
        background: rgba(var(--rgb), 1);
    }

    #profile_tab_service_reports .table > thead th.sortable {
        cursor: pointer;
    }

    #profile_tab_service_reports .table > thead th.sortable i {
        float: right;
        margin-top: 2px;
    }

    #profile_tab_service_reports .table > thead th {
        white-space: nowrap;
        border-right: 2px solid #f1f1f1;
    }

    #profile_tab_service_reports .edit-modal-dialog {
        width: 95%;
        max-width: 1200px;
    }

    #profile_tab_service_reports .edit-modal-dialog .line {
        margin-right: 0px;
    }

    #profile_tab_service_reports .edit-modal-dialog .row {
        margin-bottom: 4px;
    }

    #profile_tab_service_reports .edit-modal-dialog .row .field-col {
        padding-left: 2px;
        padding-right: 2px;
    }

    #profile_tab_service_reports .edit-modal-dialog .modal-body {
        padding-bottom: 2px;
        padding-right: 2px;
    }

    #profile_tab_service_reports .edit-modal-dialog .modal-body > .form-group > .col-sm-12 {
        padding-left: 0px;
    }

    #profile_tab_service_reports .edit-modal-dialog .modal-body > .form-group {
        margin-bottom: 0px;
        margin-right: 0px;
    }

    #profile_tab_service_reports .btn-file {
        width: 100%;
    }

    #profile_tab_service_reports .btn-file .action-upload {
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

    #profile_tab_service_reports .hidden-on-tab {
        display: none;
    }

</style>
<section id="profile_tab_service_reports" class="col-sm-12 panel panel-default p-n"
         diez-app="EquipmentServiceReportsApp"
         diez-src="equipment/components/service-reports.js"
         diez-deferred="true"
         data-equipment-id="<?php echo $eq->eq_id; ?>">
    <header class="panel-heading row m-r-none">
        <div class="col-sm-6">
            <div class="btn-group">
                <button type="button" class="action-refresh btn btn-sm btn-default" title="Refresh"><i
                            class="fa fa-refresh"></i></button>
            </div>

        </div>
        <div class="col-sm-4 m-t-xs">
            Service Reports
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
                <th class="sortable" data-sort="service_id">Service Name</th>
                <th class="sortable" data-sort="service_report_type">Report Type</th>
                <th class="sortable" data-sort="service_report_note">Note</th>
                <th><?php echo ucfirst(DISTANCE_MEASUREMENT); ?>/hrs</th>
                <th class="sortable" data-sort="service_report_created_at">Created At</th>
                <th class="sortable" data-sort="service_report_postponed_to">Postponed to</th>
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
                    <header class="panel-heading">Edit Report</header>
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                        <button class="btn btn-info" type="submit" style="30px"><span
                                    class="btntext">Save</span></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div id="view" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" class="form-horizontal">
                <div class="modal-content panel panel-default p-n">
                    <header class="panel-heading">Edit Report</header>
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<?php $this->load->view_hb('service_report_row'); ?>
<?php $this->load->view_hb('service_report_edit'); ?>
<?php $this->load->view_hb('profile_tab_service_report_view'); ?>
<?php $this->load->view_hb('includes/paginator'); ?>

<script>
    var isAdmin = <?php echo isAdmin() ? 'true' : 'false' ?>;
    var PAGE_NUM_URI_SEGMENT = 3;
    var dateFormat = "<?php echo config_item('dateFormat'); ?>";
    var postponeReportType = <?php echo EquipmentServiceReport::TYPE_POSTPONED ?>;
    var allTaxes = <?php echo json_encode(all_taxes()); ?>;
    var defaultTax = <?php echo json_encode(getDefaultTax()); ?>;
</script>

    
