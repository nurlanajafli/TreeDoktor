<?php $this->load->view('includes/header'); ?>


<link rel="stylesheet" href="https://cdn.datatables.net/1.10.23/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>

<style>
    #trees-table th {
        background-color: #f5f5f5;
    }
    .datatable-footer {
        background-color: #f5f5f5;
    }

    .datatable-footer  div {
        border-top: 1px solid #f1f1f1;
        background-color: #f5f5f5;
    }
    #trees-table-container #trees-table_wrapper {
        background-color: #f5f5f5;
    }
    #trees-table {
        border-bottom: 2px solid #e4e4e4;
    }
</style>

<section class="scrollable p-sides-10" style="margin: 0">
    <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
        <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
        <li class="active">Tree Types</li>
    </ul>
    <section class="panel panel-default">
        <header class="panel-heading">
            Tree Types <span class="small">total: <span class="js-total-trees"></span></span>

            <div class="pull-right p-right-10">
                <div class="p-left-10">
                    <a class="btn btn-xs btn-success btn-mini m-right-10 pull-right js-create-tree-btn" type="button"><i class="fa fa-plus"></i></a>
                </div>
            </div>

            <div class="pull-right" style="margin-top: -6px;">
                <div class="input-group">
                    <input name="search" id="search-trees-input" type="text" class="input-sm form-control" placeholder="Search Trees">
                    <span class="input-group-btn">
                        <button class="btn btn-sm btn-default"  id="search-tree-btn">Go!</button>
                    </span>
                </div>
            </div>
        </header>

        <div id="trees-table-container">
            <table id="trees-table" class="table table-striped b-t">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>English Name</th>
                        <th>Latin Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</section>

<!-- Edit trees modal -->
<div id="edit-trees-modal" class="modal fade" tabindex="-1" role="dialog" >
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading"><i class="glyphicon glyphicon-tree-deciduous text-success"></i> Edit Tree</header>

            <form id="edit-trees-form">
                <div class="modal-body p-10">
                    <div class="cards-info"></div>

                    <div class="form-group">
                        <label for="edit-trees-english-name-input">English Name</label>
                        <input type="text" class="form-control" id="edit-trees-english-name-input" name="trees_name_eng" placeholder="English Name" value="">
                    </div>
                    <div class="form-group">
                        <label for="edit-trees-latin-name-input">Latin Name</label>
                        <input type="text" class="form-control" id="edit-trees-latin-name-input"  name="trees_name_lat" placeholder="Latin Name" value="">
                    </div>

                    <input type="hidden" name="trees_id" id="trees-id-input" value="">
                </div>
            </form>

            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                <button class="btn btn-success pull-right js-update-tree">Update</button>
            </div>
        </div>
    </div>
</div>
<!-- Edit trees modal end-->

<!-- Create trees modal -->
<div id="create-trees-modal" class="modal fade" tabindex="-1" role="dialog" >
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading"><i class="glyphicon glyphicon-tree-deciduous text-success"></i> Create tree</header>

            <form id="create-trees-form">
                <div class="modal-body p-10">
                    <div class="cards-info"></div>

                    <div class="form-group">
                        <label for="trees-english-name-input">English Name</label>
                        <input type="text" class="form-control" id="trees-english-name-input" name="trees_name_eng" placeholder="English Name"  value="">
                    </div>
                    <div class="form-group">
                        <label for="trees-latin-name-input">Latin Name</label>
                        <input type="text" class="form-control" id="trees-latin-name-input"  name="trees_name_lat" placeholder="Latin Name"  value="">
                    </div>
                </div>
            </form>

            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                <button class="btn btn-success pull-right js-create-tree">Create</button>
            </div>
        </div>
    </div>
</div>
<!-- Create trees modal end-->

<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/modules/leads/tree_inventory/tree_types.js?v=1"></script>
<?php $this->load->view('includes/footer'); ?>