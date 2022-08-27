<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url('assets/vendors/notebook/js/sortable/jquery.sortable.js'); ?>"></script>
<style>.sortable-placeholder {
        min-height: 54px;
    }</style>
<section class="scrollable p-sides-15">
    <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
        <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
        <li class="active">Bundles</li>
    </ul>
    <section class="panel panel-default">
        <header class="panel-heading">Bundles
            <a href="#bundle-modal-new" class="btn btn-xs btn-success pull-right bundleModal" role="button" data-toggle="modal"
               data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i></a>
        </header>
        <div class="table-responsive">
            <?php
            /*
            foreach ($products as $product) : ?>
                <?php $this->load->view('partials/service_modal', ['service' => $product]); ?>
            <?php endforeach;
            */
            ?>
            <ul class="list-group gutter list-group-lg list-group-sp">
                <?php if(!isset($bundles) || !count($bundles)): ?>
                    <li class="clear list-group-item">
                        <div class="col-md-12 text-center">Bundles list is empty<br><a href="#bundle-modal-new" class="bundleModal" role="button" data-toggle="modal"
                                                                                        data-backdrop="static" data-keyboard="false">Create bundle</a></div>
                    </li>
                <?php else: ?>
                    <?php foreach ($bundles as $bundle) : ?>
                        <li data-id="<?php echo $bundle->service_id; ?>" class="clear list-group-item">
                            <?php $style = $bundle->service_status ? '' : ' style="text-decoration: line-through;"'; ?>
                            <div class="col-md-3"<?php echo $style; ?>><i
                                    class="fa fa-sort text-muted fa m-r-sm"></i><?php echo $bundle->service_name; ?></div>
                            <div class="col-md-6"<?php echo $style; ?>><?php echo $bundle->service_description; ?></div>
                            <div class="col-md-2"<?php echo $style; ?>>
                                <?php echo money($bundle->cost); ?>
                            </div>
                            <div class="col-md-1">
                                <form data-url="<?php echo base_url('estimates/bundles/edit'); ?>" data-type="ajax" data-callback="window.edit_modal" class="pull-left">
                                    <input type="hidden" name="service_id" value="<?php echo $bundle->service_id; ?>">
                                    <button class="btn btn-xs btn-default editBundleModal"><i class="fa fa-pencil"></i></button>
                                </form>
                                <form id="delete-product-<?php echo $bundle->service_id; ?>" data-url="<?php echo base_url('estimates/bundles/delete'); ?>" data-type="ajax" data-location="<?php echo base_url('estimates/bundles'); ?>" class="pull-left">
                                    <input type="hidden" name="service_id" value="<?php echo $bundle->service_id; ?>">
                                    <input type="hidden" name="status" value="<?php echo $bundle->service_status; ?>">

                                    <button class="btn btn-xs btn-info deleteService" data-href="#delete-product-<?php echo $bundle->service_id; ?>" data-title="<?php echo $bundle->service_name; ?>">
                                        <i class="fa <?php if ($bundle->service_status) : ?>fa-eye-slash<?php else : ?>fa-eye<?php endif; ?>"></i>
                                    </button>
                                </form>
                                <?php $this->load->view('qb/partials/qb_logs', [ 'lastQbTimeLog' => $bundle->service_last_qb_time_log, 'lastQbSyncResult' => $bundle->service_last_qb_sync_result, 'module' => 'item', 'entityId' => $bundle->service_id, 'entityQbId' => $bundle->service_qb_id, 'bundle' => true]); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </section>
</section>
<textarea  hidden class="allBundles"> <?php echo json_encode($allBundles); ?> </textarea>

<div id="bundle-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <?php $this->load->view('bundles/bundle_form', ['bundle' => []]); ?>
        </div>
    </div>
</div>

<div id="bundle-modal-new" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <?php $this->load->view('bundles/bundle_form', ['bundle' => []]); ?>
        </div>
    </div>
</div>
<script>
    
    window.edit_modal = function(response){
        $('#bundle-modal .modal-content').html(response.html);
        $('#bundle-modal').modal();
        setSelect2();
        Common.mask_currency();
    };

    $(document).ready(function (){
        $(document).on('click', '.favourite-checkbox', function () {
            let item_id = $(this).data('item_id');
            let modal = $(this).closest('form');
            if(item_id === ''){
                let item_name = modal.find('.bundle_name').val();
                if(item_name.trim() !== ''){
                    let first_letters = '';
                    let item_name_array = item_name.trim().split(' ');
                    if(item_name_array[0] && item_name_array[1])
                        first_letters = item_name_array[0].substr(0,1).toUpperCase() + item_name_array[1].substr(0,1).toUpperCase();
                    else if(item_name_array[0])
                        first_letters = item_name_array[0].substr(0,2).toUpperCase();
                    modal.find('.first-letters').text(first_letters);
                    modal.find('.first-letters').closest('label').show();
                } else {
                    modal.find("input[value='tree-removal']").prop('checked', true);
                    modal.find('.first-letters').closest('label').hide();
                }
            }
            modal.find('.favourite-icons-' + item_id).toggle();
        });
        if($(".gutter").children().length > 1) {
            initQbLogPopover();
        }
        Common.mask_currency();

        $('.deleteService').click(function(){
            if (confirm('Are you sure?')) {
                var id = $(this).data("href");
                $(id).trigger("submit");
            }
            return false;
        });

        $('.sortable').sortable().bind('sortupdate', function () {
            var arr = [];
            $.each($('.sortable').children(), function (key, val) {
                priority = key + 1;
                arr[$(val).data('id')] = priority;
            });
            $.post(baseUrl + 'estimates/ajax_estimate_priority_service', {data: arr}, function (resp) {
                if (resp.status == 'error')
                    alert('Ooops! Error...');
                return false;
            }, 'json');
            return false;
        });
        $(document).on('click', '.addBundleProd',function () {
            let form = $(this).closest('form');
            let length = form.find('.table tr').size();
            let lastSelect2 = '';
            if(length > 1)
                lastSelect2 = $(form.find('.table tbody tr')[length-2].children[1].children[0]).select2('val');
            if(length == 1 || lastSelect2) {
                let tr = '<tr>\n' +
                    '                        <th scope="row"> ' + length + '</th>\n' +
                    '                        <td><input type="text" class="select2 w-200"></td>\n' +
                    '                        <td><input type="number" class="form-control text-center" onchange="handleChange(this);" style="width: 70px!important;" value="1"></td>\n' +
                    '                        <td>\n' +
                    '                            <a href="#" class="btn btn-danger remove" >\n' +
                    '                                <i class="fa fa-trash-o"></i>\n' +
                    '                            </a>\n' +
                    '                             <a href="#" class="btn btn-success addBundleProd">\n' +
                    '                                <i class="fa fa-plus"></i>\n' +
                    '                             </a> ' +
                    '                       </td>\n' +
                    '                    </tr>';
                form.find('.table').append(tr);
                setSelect2();
                $(form.find('.table tbody tr')[length-2]).find('.addBundleProd').remove();
            }
            if(lastSelect2 == '' && length > 1){
                $(form.find('.table tbody tr')[length-2].children[1].children[0].children[0]).css("border-color","red");
            }
        });
        $('.bundleModal, .editBundleModal').click(function () {
            setSelect2();
        });
        $(document).on('click', '.remove', function(){
           let form = $(this).closest('form');
            if(form.find('.table tbody tr').size() > 1) {
                $(this).parent().parent().remove();
                refreshNumbersTable(form);
            }
        });
        $(document).on('click', '.saveBundle', function(){
            let form = $(this).closest('form');
            let tableData = getTableData(form);
            if(tableData.length)
                form.find('input[name="bundle_services"]').val(tableData);
            // $("#saveForm").submit();
            $(this).closest('form').submit();
        });
    });
    function handleChange(input) {
        if (input.value < 1) input.value = 1;
        if (input.value > 999) input.value = 999;
    }
    function setSelect2(){
        let data_json = $('.allBundles').text();
        if(typeof(data_json) != "undefined") {
            let data = JSON.parse(data_json);
            $('.select2').select2({data: data}).on("change", function(e) {
                $(this).parent().find('a').css("border-color","#aaaaaa");
            });
        }
    }
    function getTableData(form) {
        let objJSON=[];
        if(form) {
            for (i = 0; i < form.find('.table tbody tr').size(); i++) {
                let selectId = $(form.find('.table tbody tr')[i].children[1].children[0]).select2('val');
                if (selectId)
                    objJSON.push
                    ({
                        id: selectId,
                        qty: $(form.find('.table tbody tr')[i].children[2].children[0]).val(),
                    });
            }
        }
        return JSON.stringify(objJSON);
    }
    function refreshNumbersTable(form) {
        if(form) {
            for (i = 0; i < form.find('.table tbody tr').size(); i++) {
                $(form.find('.table tbody tr')[i].children[0]).text(i + 1);
                if (i + 1 == form.find('.table tbody tr').size() && !form.find('a').is('.addBundleProd')) {
                    $(form.find('.table tbody tr')[i].children[3]).append(' <a href="#" class="btn btn-success addBundleProd">\n' +
                        '                                                <i class="fa fa-plus"></i>\n' +
                        '                                             </a> ');
                }
            }
        }
    }

</script>
<?php $this->load->view('includes/footer'); ?>
