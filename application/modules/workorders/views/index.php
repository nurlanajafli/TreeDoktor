<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url('assets/js/jquery.tablesorter.min.js'); ?>"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap.min.css">
<script src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>
<style>
    .select2-container {
        display: flex;
        height: unset !important;
    }
    table.dataTable{ margin-top: -1px!important; min-width: 100%; }
</style>
<!--Title -->
<section class="scrollable p-sides-15">
    <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
        <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
        <li class="active">Workorders</li>
    </ul>
    <!-- Estimates header -->
    <section class="panel panel-default" style="min-height: calc(100% - 64px);">
        <header class="panel-heading wo_status_filter">Workorders
            <a href="<?php echo base_url('workorders/workorders_mapper'); ?>/" id="statusMapper" role="button"
               class="btn btn-xs btn-dark pull-right" style="margin-top: 2px" target="_blank"><i
                        class="fa fa-map-marker"></i></a>
            <!-- Search Form -->

            <div class="pull-right filter-container p-right-10" style="">
                <button class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-filter"></i>
                    <span class="caret" style="margin-left:5px;"></span>
                </button>
                <div class="filter dropdown-menu animated fadeInDown" style="min-width:350px;padding:0px">
                    <?php $this->load->view('partials/workorders_search_block');?>
                </div>
            </div>
            <div class="btn-group pull-right p-right-10" style="">
                <button class="btn	 btn-info dropdown-toggle wo_status_btn" data-toggle="dropdown">Statuses</button>
                <ul class="dropdown-menu" id="wo_status" data-type="workorders">
                    <li class="active"><a href="#tab-1" data-toggle="tab" data-statusname="-1" style="padding-right: 6px;padding-left: 6px;">All <span class="badge bg-info"><?//= $total; ?></span></a></li>
                </ul>
            </div>

            <div class="clear"></div>
        </header>
        <div id="trees-table-container">
            <table id="trees-table" class="table table-striped b-t">
                <thead>
                <tr>
                    <th style="width: 150px;">Client Name</th>
                    <th style="min-width: 50px;">Tags</th>
                    <th style="min-width: 38px;">No</th>
                    <th style="min-width: 70px; width: 70px;" >Date</th>
                    <th style="width: 140px;">Address</th>
                    <th style="min-width: 110px; width: 110px;">Phone</th>
                    <th style="min-width: 150px;" width="20%">Estimator</th>
                    <th style="min-width: 100px;" class="text-center">Total</th>
                    <th style="min-width: 250px; width: 250px;" class="text-center">Notes</th>
                    <th style="min-width: 1px;max-width: 30px;">Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </section>
</section>
<script src="<?php echo base_url(); ?>assets/js/modules/workorders/workorders_list.js?v=1.25"></script>
<script src="<?php echo base_url(); ?>assets/js/modules/workorders/workorders.js?v=<?php echo config_item('js_workorders'); ?>"></script>
<!-- /Title ends -->
<script>
    var tagsExpandLimit = `<?= $tagsExpandLimit ?>`;
</script>
<?php $this->load->view('includes/footer'); ?>
