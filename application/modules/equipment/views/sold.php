<?php
/**
 * @var \application\modules\equipment\models\EquipmentGroup|boolean $group
 */

use application\modules\equipment\models\Equipment;

?>
<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/select2/select2.css'); ?>"
      type="text/css"/>
<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/select2/theme.css'); ?>" type="text/css"/>
<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/equipment/equipment.css'); ?>"
      type="text/css"/>
<style>
    #equipment .datepicker {
        -webkit-border-radius: 2px;
        -moz-border-radius: 2px;
        border-radius: 2px;
    }

    #equipment .truncate {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (min-width: 768px) {
        #equipment .panel-heading .buttons {
            text-align: right;
        }
    }

    #equipment .panel > .row {
        margin-left: initial;
        margin-right: initial;
    }

    #equipment .data-list tr {
        --rgb: 255, 255, 255;
        background: rgba(var(--rgb), .8);
        white-space: nowrap;
    }

    #equipment .data-list tr:hover {
        --rgb: 245, 245, 245;
        background: rgba(var(--rgb), 1);
    }

    #equipment .table > thead th.sortable {
        cursor: pointer;
    }

    #equipment .table > thead th.sortable i {
        float: right;
        margin-top: 2px;
    }

    #equipment .table > thead th {
        white-space: nowrap;
        border-right: 2px solid #f1f1f1;
    }

    #equipment {
        overflow-x: auto;
        overflow-y: auto;
        margin-top: 34px;
    }

    #equipment > header {
        padding-right: 0px;
        padding-left: 0px;
    }

    #equipment > header > .nav {
        display: inline-block;
        padding-left: 25px;
        padding-right: 25px;
    }

    #equipment > section {
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
</style>
<!-- All clients display -->
<ul class="breadcrumb no-border no-radius b-b b-light">
    <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
    <li class="active">Sold Equipment <?php echo $group ? "in " . $group->group_name : ""; ?></li>
</ul>
<section id="equipment" diez-app="EquipmentSoldApp" diez-src="equipment/components/sold.js"
    <?php echo $group ? 'data-group-id="' . $group->group_id . '"' : ''; ?>
         class="scrollable p-sides-15">

    <section class="col-sm-12 panel panel-default p-n">
        <header class="panel-heading row">
            <div class="col-sm-2">
                <div class="btn-group">
                    <button type="button" d-event="refresh" class="action-refresh btn btn-sm btn-default"
                            title="Refresh"><i
                                class="fa fa-refresh"></i></button>
                    <button type="button" class="action-create btn btn-sm btn-default" title="Create Equipment"><i
                                class="fa fa-plus"></i></button>
                </div>
            </div>
            <div class="col-sm-4 v-middle">
                Equipment in
                <select name="filter[group_id]" class="action-filter input-sm form-control input-s inline v-middle">
                    <option value="">All</option>
                    <?php foreach ($groups as $g): ?>
                        <option value="<?= $g->group_id ?>"
                            <?= $group && $group->group_id === $g->group_id ? 'selected="selected"' : "" ?>>
                            <?= $g->group_name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" class="input-query input-sm form-control" placeholder="Filter by Name">
                    <span class="input-group-btn">
                        <button class="action-query btn btn-sm btn-default" type="button">Go!</button>
                      </span>
                </div>
            </div>
            <div class="col-sm-2">
            </div>
        </header>
        <div class="table-responsive">
            <!-- Data display -->
            <table class="table" id="tbl_search_result">
                <thead>
                <tr>
                    <th class="sortable" data-sort="group_id">Group</th>
                    <th class="sortable" data-sort="eq_name">Name</th>
                    <th class="sortable" data-sort="eq_sold_code">Old code</th>
                    <th class="sortable" data-sort="eq_serial">Serial</th>
                    <th class="sortable" data-sort="eq_description">Description</th>
                    <th class="sortable" data-sort="eq_created_at">Created on</th>
                    <th class="sortable" data-sort="eq_sold_at">Sold on</th>
                    <th class="sortable" data-sort="eq_sold_cost">Sold cost</th>
                    <th class="sortable" data-sort="seller_id">Sold by</th>
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
                    <header class="panel-heading">Edit Equipment</header>
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


<?php $this->load->view_hb('sold_equipment_row'); ?>
<?php $this->load->view_hb('unsold'); ?>
<?php $this->load->view_hb('profile_edit'); ?>
<?php $this->load->view_hb('includes/paginator'); ?>

<script>
    var isAdmin = <?php echo isAdmin() ? 'true' : 'false' ?>;
    var PAGE_NUM_URI_SEGMENT = 3;
    var currentUserData = <?php echo json_encode(request()->user()->load(['employee'])); ?>;
    var eqCounterTypes = <?php echo json_encode(Equipment::COUNTER_TYPES); ?>;
    var DATE_FORMAT = "<?php echo getJSDateFormat(); ?>";
</script>

<?php $this->load->view('includes/footer'); ?>

    
