<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/css/colpick.css'); ?>"/>
<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/equipment/equipment.css'); ?>"
      type="text/css"/>
<style>
    #equipment_groups .truncate {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (min-width: 768px) {
        #equipment_groups .panel-heading .buttons {
            text-align: right;
        }
    }

    #equipment_groups .panel > .row {
        margin-left: initial;
        margin-right: initial;
    }

    #equipment_groups .mycolorpicker {
        cursor: pointer;
    }

    #equipment_groups .data-list tr {
        --rgb: 255, 255, 255;
        background: rgba(var(--rgb), .8);
        white-space: nowrap;
    }

    #equipment_groups .data-list tr:hover {
        --rgb: 245, 245, 245;
        background: rgba(var(--rgb), 1);
    }

    #equipment_groups .table > thead th.sortable {
        cursor: pointer;
    }

    #equipment_groups .table > thead th.sortable i {
        float: right;
        margin-top: 2px;
    }

    #equipment_groups .table > thead th {
        white-space: nowrap;
        border-right: 2px solid #f1f1f1;
    }

    #equipment_groups {
        overflow-x: auto;
        overflow-y: auto;
        margin-top: 34px;
    }

    #equipment_groups > header {
        padding-right: 0px;
        padding-left: 0px;
    }

    #equipment_groups > header > .nav {
        display: inline-block;
        padding-left: 25px;
        padding-right: 25px;
    }

    #equipment_groups > section {
        display: inline-table;
    }


    #equipment_groups .table > thead th.sortable {
        cursor: pointer;
    }

    #equipment_groups .table > thead th.sortable i {
        float: right;
        margin-top: 2px;
    }

    #equipment_groups .table > thead th {
        border-right: 2px solid #f1f1f1;
    }
</style>
<!-- All clients display -->
<ul class="breadcrumb no-border no-radius b-b b-light">
    <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
    <li class="active">Equipment Groups</li>
</ul>
<section id="equipment_groups" diez-app="EquipmentGroupsApp" diez-src="equipment/components/groups.js"
         class="scrollable p-sides-15">

    <section class="col-sm-12 panel panel-default p-n">
        <header class="panel-heading row">
            <div class="col-sm-2">
                <div class="btn-group">
                    <button type="button" class="action-refresh btn btn-sm btn-default"
                            title="Refresh"><i
                                class="fa fa-refresh"></i></button>
                    <button type="button" class="action-create btn btn-sm btn-default" title="Create Equipment Group"><i
                                class="fa fa-plus"></i></button>
                </div>
            </div>
            <div class="col-sm-10">
            </div>
        </header>
        <div class="table-responsive">
            <!-- Data display -->
            <table class="table" id="tbl_search_result">
                <thead>
                <tr>
                    <th style="width: 45px;">#</th>
                    <th class="sortable" data-sort="group_name">Name</th>
                    <th class="sortable" data-sort="group_prefix">Prefix</th>
                    <th class="sortable" data-sort="group_created_at" style="width: 150px;">Created On</th>
                    <th style="width: 100px;">Actions</th>
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
                    <header class="panel-heading">Edit Group</header>
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


<?php $this->load->view_hb('group_row'); ?>
<?php $this->load->view_hb('group_edit'); ?>
<?php $this->load->view_hb('includes/paginator'); ?>

<script>
    var isAdmin = <?php echo isAdmin() ? 'true' : 'false' ?>;
    var PAGE_NUM_URI_SEGMENT = 3;
</script>

<?php $this->load->view('includes/footer'); ?>

    
