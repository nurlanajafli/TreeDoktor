<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap.min.css">

<style>
    .select2-container {
        display: flex;
        height: unset !important;
    }

    table.dataTable {
        margin-top: -1px !important;
        min-width: 100%;
    }
</style>

<!-- All clients display -->
<section class="scrollable p-sides-10">
    <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
        <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
        <li class="active">Clients</li>
    </ul>
    <section class="panel panel-default" style="min-height: calc(100% - 55px); margin-bottom: 0;">
        <header class="panel-heading"
                style="height: 40px; padding: 0 15px; display: flex; align-items: center; flex-direction: row;">
            <div class="row" style="width: 110%;">
                <div class="col-md-3 col-sm-3 hidden-xs" style="height: 40px; display: flex; align-items: center;">
                    Clients
                </div>
                <div class="pull-right"
                     style="flex-direction: row-reverse; display: flex; align-items: center; height: 40px;">
                    <div class="pull-right p-right-10"
                         style="display: flex; flex-direction: row-reverse;">
                        <div class="btn-group pull-right clients-filter-button" style="">
                            <!-- Search Estimates -->
                            <?php $this->load->view('clients/client_search_estimates'); ?>
                        </div>
                        <?php echo anchor('clients/new_client', '<i class="fa fa-plus"></i><i class="fa fa-user"></i>', 'class="btn btn-xs btn-success btn-mini m-right-10 pull-right" style="display: flex; align-items: center;" type="button"'); ?>
                        <?php if(isAdmin()) : ?>
                            <?php echo anchor('clients#', '<i class="fa fa-download"></i>', 'id="csvExport" class="btn btn-xs btn-default btn-mini m-right-10 pull-right p-sides-10" title="Export To CSV" style="display: flex; align-items: center;" type="button"'); ?>
                        <?php endif; ?>
                    </div>
                    <div class="pull-right" style="margin-right: 10px;">
                        <?php $this->load->view('partials/client_search'); ?>
                    </div>
                </div>
            </div>
        </header>

        <div id="trees-table-container">
            <table id="trees-table" class="table table-striped b-t">
                <thead>
                <tr>
                    <th>ID</th>
                    <th class="clients-type__th" style="min-width: 31px; max-width: 40px; padding-left: 35px;">Type</th>
                    <th class="clients-name__th" style="min-width: 150px; padding-left: 35px;" width="15%">Name</th>
                    <th style="min-width: 150px;" width="20%">Tags</th>
                    <th style="min-width: 100px;">Phone Number</th>
                    <th style="min-width: 150px;">Address</th>
                    <th style="min-width: 100px;" class="text-center">Sales</th>
                    <th style="max-width: 1px; min-width: 1px;"></th>
                </tr>
                </thead>
            </table>
        </div>
    </section>
</section>

<script>
  let tagsExpandLimit = '<?php echo $tagsExpandLimit ?>';
</script>

<script src="<?php echo base_url(); ?>assets/js/modules/clients/clients_list.js?v=1.11"></script>
<script src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>

<?php $this->load->view('includes/footer'); ?>
