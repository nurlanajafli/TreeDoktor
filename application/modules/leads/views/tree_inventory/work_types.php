<?php $this->load->view('includes/header'); ?>


    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.23/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>

    <style>
        #work-types-table th {
            background-color: #f5f5f5;
        }
        .datatable-footer {
            background-color: #f5f5f5;
        }

        .datatable-footer  div {
            border-top: 1px solid #f1f1f1;
            background-color: #f5f5f5;
        }
        #work-types-table-container #work-types-table_wrapper {
            background-color: #f5f5f5;
        }
        #work-types-table {
            border-bottom: 2px solid #e4e4e4;
        }
    </style>

    <section class="scrollable p-sides-10" style="margin: 0">
        <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
            <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Work Types</li>
        </ul>
        <section class="panel panel-default">
            <header class="panel-heading">
                Work Types <span class="small">total: <span class="js-total-work-types"></span></span>

                <div class="pull-right p-right-10">
                    <div class="p-left-10">
                        <a class="btn btn-xs btn-success btn-mini m-right-10 pull-right js-create-work-types-btn" type="button"><i class="fa fa-plus"></i></a>
                    </div>
                </div>

                <div class="pull-right" style="margin-top: -6px;">
                    <div class="input-group">
                        <input name="search" id="search-work-types-input" type="text" class="input-sm form-control" placeholder="Search Work Types">
                        <span class="input-group-btn">
                        <button class="btn btn-sm btn-default"  id="search-work-types-btn">Go!</button>
                    </span>
                    </div>
                </div>
            </header>

            <div id="work-types-table-container">
                <table id="work-types-table" class="table table-striped b-t">
                    <thead>
                    <tr>
                        <th>id</th>
                        <th>Short Name</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </section>
    </section>

    <!-- Edit work types modal -->
    <div id="edit-work-types-modal" class="modal fade" tabindex="-1" role="dialog" >
        <div class="modal-dialog">
            <div class="modal-content panel panel-default p-n">
                <header class="panel-heading">Edit Work Type</header>

                <form id="edit-work-types-form">
                    <div class="modal-body p-10">
                        <div class="cards-info"></div>

                        <div class="form-group">
                            <label for="edit-work-type-ip-name-short">Short Name</label>
                            <input type="text" class="form-control" id="edit-work-type-ip-name-short" name="ip_name_short" placeholder="Short Name" value="">
                        </div>
                        <div class="form-group">
                            <label for="edit-work-type-ip-name-input">Name</label>
                            <input type="text" class="form-control" id="edit-work-type-ip-name-input"  name="ip_name" placeholder="Name" value="">
                        </div>

                        <input type="hidden" name="ip_id" id="work-types-ip-id-input" value="">
                    </div>
                </form>

                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn btn-success pull-right js-update-work-type">Update</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit work types modal end-->

    <!-- Create work types modal -->
    <div id="create-work-type-modal" class="modal fade" tabindex="-1" role="dialog" >
        <div class="modal-dialog">
            <div class="modal-content panel panel-default p-n">
                <header class="panel-heading">Create Work Type</header>

                <form id="create-work-type-form">
                    <div class="modal-body p-10">
                        <div class="cards-info"></div>

                        <div class="form-group">
                            <label for="work-type-ip-name-short">Short Name</label>
                            <input type="text" class="form-control" id="work-type-ip-name-short" name="ip_name_short" placeholder="Short Name"  value="">
                        </div>
                        <div class="form-group">
                            <label for="work-type-ip-name-input">Name</label>
                            <input type="text" class="form-control" id="work-type-ip-name-input"  name="ip_name" placeholder="Name"  value="">
                        </div>
                    </div>
                </form>

                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn btn-success pull-right js-create-work-type">Create</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Create work types modal end-->

<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/modules/leads/tree_inventory/work_types.js?v=1"></script>
<?php $this->load->view('includes/footer'); ?>