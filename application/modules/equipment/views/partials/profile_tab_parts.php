<?php
/**
 * @var \application\modules\equipment\models\Equipment $eq
 */
?>
<style>

    #profile_tab_parts .truncate {
        max-width: 110px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (min-width: 768px) {
        #profile_tab_parts .panel-heading .buttons {
            text-align: right;
        }
    }

    #profile_tab_parts .panel > .row {
        margin-left: initial;
        margin-right: initial;
    }

    #profile_tab_parts .data-list tr {
        --rgb: 255, 255, 255;
        background: rgba(var(--rgb), .8);
    }

    #profile_tab_parts .data-list tr:hover {
        --rgb: 245, 245, 245;
        background: rgba(var(--rgb), 1);
    }

    #profile_tab_parts .table > thead th.sortable {
        cursor: pointer;
    }

    #profile_tab_parts .table > thead th.sortable i {
        float: right;
        margin-top: 2px;
    }

    #profile_tab_parts .table > thead th {
        white-space: nowrap;
        border-right: 2px solid #f1f1f1;
    }
</style>
<section id="profile_tab_parts" class="col-sm-12 panel panel-default p-n" diez-app="EquipmentProfileTabPartsApp"
         diez-src="equipment/components/profile-tab-parts.js"
         diez-deferred=true
         data-equipment-id="<?php echo $eq->eq_id; ?>">
    <header class="panel-heading row m-r-none">
        <div class="col-sm-6">
            <div class="btn-group">
                <button type="button" class="action-refresh btn btn-sm btn-default" title="Refresh"><i
                            class="fa fa-refresh"></i></button>
                <button type="button" class="action-create btn btn-sm btn-default"
                        title="Add Part to <?php echo $eq->eq_name; ?>"><i
                            class="fa fa-plus"></i></button>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="input-group">
                <input type="text" class="input-query input-sm form-control" placeholder="Filter by Name">
                <span class="input-group-btn">
                        <button class="action-query btn btn-sm btn-default" type="button">Go!</button>
                      </span>
            </div>
        </div>
        <div class="buttons col-sm-2 v-middle text-right">
        </div>
    </header>
    <div class="table-responsive">
        <!-- Data display -->
        <table class="table" id="tbl_search_result">
            <thead>
            <tr>
                <th class="sortable" data-sort="part_number">Part No</th>
                <th class="sortable" data-sort="part_name">Name</th>
                <th class="sortable" data-sort="part_description">Description</th>
                <th class="sortable" data-sort="part_price">Cost</th>
                <th class="sortable" data-sort="part_seller">Seller</th>
                <th class="sortable" data-sort="part_purchased_date">Purchased Date</th>
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
                    <header class="panel-heading">Edit Part</header>
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
</section>
<?php $this->load->view_hb('profile_tab_part_row'); ?>
<?php $this->load->view_hb('profile_tab_part_edit'); ?>
<?php $this->load->view_hb('includes/paginator'); ?>

<script>
    var isAdmin = <?php echo isAdmin() ? 'true' : 'false' ?>;
    var PAGE_NUM_URI_SEGMENT = 3;
    var dateFormat = "<?php echo config_item('dateFormat'); ?>";
</script>

    
