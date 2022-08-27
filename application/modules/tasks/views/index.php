<?php $this->load->view('includes/header'); ?>
    <link href="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/css/bootstrap-editable.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendors/notebook/js/select2/theme.css" type="text/css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap.min.css">
    <script src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>
    <style>
        #my_trees-table_info {
            padding-bottom: 15px;
        }
        .select2-container {
            display: flex;
            height: unset !important;
        }
        table.dataTable{ margin-top: -1px!important; min-width: 100%; }
    </style>

    <!-- Tasks -->
    <section class="scrollable p-sides-10">
        <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
            <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Tasks</li>
        </ul>
        <section id="tasks_section" class="panel panel-default" style="min-height: 60px; display: none;">
            <header class="panel-heading" style="height: 40px;">
                <div class="row">
                    <div class="col-md-3 col-sm-3 hidden-xs">
                        Tasks
                    </div>

                    <div class="pull-right" style="margin-top: -6px; margin-right: 10px;">
                        <a href="tasks/tasks_mapper" role="button" class="btn btn-xs btn-dark pull-right" style="margin-top: -1px"
                           target="_blank"><i class="fa fa-map-marker"></i></a>
                    </div>
                </div>
            </header>

            <div id="tasks-table-container">
                <table id="tasks-table" class="table table-striped b-t">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th style="min-width: 150px;">Client Name</th>
                        <th style="min-width: 100px;">Task Date</th>
                        <th>Address</th>
                        <th>Created By</th>
                        <th>Task Category</th>
                        <th>Status</th>
                        <th>Map</th>
                        <th style="min-width: 110px;">Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </section>
        <div id="modal_container"></div>
    </section>

    <!-- Tasks -->

<script src="<?php echo base_url(); ?>assets/js/modules/tasks/tasks_list.js?v=1.0"></script>

<?php $this->load->view('includes/footer'); ?>