<form name="search" id="wo-search-form">

    <input type="hidden" name="count_estimators" value="{{:~object_length(estimators)}}">
    <input type="hidden" name="count_crews" value="{{:~object_length(crews)}}">
    <input type="hidden" name="count_services" value="{{:~object_length(services)}}">

    <section class="panel panel-default portlet-item" style="opacity: 1; margin-bottom:0px;">
        <section class="panel-body">
            <article class="media m-top-10 d-none">
                <div class="media-body" style="margin-top: -5px;">
                    <div class="row">
                        <div class="col-md-12">
                            <span class="h6"><i class="fa fa-user fa-1x icon-muted"></i> Status</span>
                            <span class="pull-right" style="font-size: 15px;"><i class="count-wo fa "></i></span>
                            <small class="block">
                                <select name="wo_status_id" class="form-control">
                                    <option value="-1" >All</option>
                                    <?php if(isset($statuses)) : ?>
                                        <?php foreach($statuses as $k=>$v) : ?>
                                            <option value="<?php echo $v->wo_status_id; ?>" <?php if(isset($default_status) && $default_status->wo_status_id == $v->wo_status_id) : ?> selected="selected"<?php endif; ?>><?php echo $v->wo_status_name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif;?>
                                </select>
                            </small>

                        </div>
                    </div>

                </div>
            </article>
            <div class="line pull-in"></div>
            <article class="media m-top-10">
                <div class="media-body" style="margin-top: -5px;">
                    <div class="row">
                        <div class="col-md-12">
                            <span class="h6"><i class="fa fa-bookmark fa-1x icon-muted"></i> Tags:</span>
                            <small class="block">

                                <input name="search_tags" class="input-sm w-100  js-tags-select2" style="padding: 0px;" value="<?= isset($search_tags) ? $search_tags : '' ?>">

                            </small>
                        </div>
                    </div>

                </div>
            </article>
            <div class="line pull-in"></div>
            <article class="media m-top-10">
                <div class="media-body" style="margin-top: -5px;">
                    <div class="row">
                        <div class="col-md-12">
                            <span class="h6"><i class="fa fa-user fa-1x icon-muted"></i> Estimators:</span>
                            <small class="block">
                                <input name="filter_estimator"  class="items-filter form-control input-sm   w-100  js-estimators-select2" style="padding: 0px;" value="<?= isset($search_estimators) ? $search_estimators : '' ?>">
                            </small>
                        </div>
                    </div>

                </div>
            </article>
            <div class="line pull-in"></div>
            <article class="media m-top-10">
                <div class="media-body" style="margin-top: -5px;">
                    <div class="row">
                        <div class="col-md-12">
                            <span class="h6"><i class="fa fa-group fa-1x icon-muted"></i> Specialists:</span>
                            <small class="block">
                                <input name="filter_crew[]" multiple class="items-filter form-control input-sm   w-100  js-crews-select2" style="padding: 0px;" value="<?= isset($search_crews) ? $search_crews : '' ?>">
                            </small>
                        </div>
                        <!--<div class="col-md-1">
                            <a class="pull-right items-filter-clear p-right-5 p-left-5" data-model="filter_crew" style="margin-bottom: -5px;"><small><i class="fa fa-times"></i></small></a>
                            <div class="clear"></div>
                            <div class="filter-item-view" id="filter-selected-crews"></div>
                        </div>-->
                    </div>
            </article>

            <div class="line pull-in"></div>
            <article class="media m-top-10">
                <div class="media-body" style="margin-top: -5px;">
                    <div class="row">
                        <div class="col-md-12">
                            <span class="h6"><i class="fa fa-wrench fa-1x icon-muted"></i> Services:</span>
                            <small class="block">
                                <input name="filter_service" class="items-filter form-control input-sm   w-100  js-services-select2" style="padding: 0px;" value="<?= isset($search_services) ? $search_services : '' ?>">

                            </small>
                        </div>
                        <!--<div class="col-md-1">
                            <a class="pull-right items-filter-clear p-right-5 p-left-5" data-model="filter_service" style="margin-bottom: -5px;"><small><i class="fa fa-times"></i></small></a>
                            <div class="clear"></div>
                            <div class="filter-item-view" id="filter-selected-services"></div>
                        </div>-->
                    </div>
            </article>

            <div class="line pull-in"></div>
            <article class="media m-top-10">
                <div class="media-body" style="margin-top: -5px;">
                    <div class="row">
                        <div class="col-md-12">
                            <span class="h6"><i class="fa fa-shopping-cart fa-1x icon-muted"></i> Products:</span>
                            <small class="block">
                                <input name="filter_product" class="items-filter form-control input-sm   w-100  js-products-select2" style="padding: 0px;" value="<?= isset($search_products) ? $search_products : '' ?>">
                            </small>
                        </div>
                        <!--<div class="col-md-1">
                            <a class="pull-right items-filter-clear p-right-5 p-left-5" data-model="filter_product" style="margin-bottom: -5px;"><small><i class="fa fa-times"></i></small></a>
                            <div class="clear"></div>
                            <div class="filter-item-view" id="filter-selected-products"></div>
                        </div>-->
                    </div>
            </article>

            <div class="line pull-in"></div>
            <article class="media m-top-10">
                <div class="media-body" style="margin-top: -5px;">
                    <div class="row">
                        <div class="col-md-12">
                            <span class="h6"><i class="fa fa-gift fa-1x icon-muted"></i> Bundles:</span>
                            <small class="block">
                                <input name="filter_bundle" class="items-filter form-control input-sm   w-100  js-bundles-select2" style="padding: 0px;" value="<?= isset($search_bundles) ? $search_bundles : '' ?>">
                            </small>
                        </div>
                        <!--<div class="col-md-1">
                            <a class="pull-right items-filter-clear p-right-5 p-left-5" data-model="filter_bundle" style="margin-bottom: -5px;"><small><i class="fa fa-times"></i></small></a>
                            <div class="clear"></div>
                            <div class="filter-item-view" id="filter-selected-bundles"></div>
                        </div>-->
                    </div>
            </article>

        </section>
    </section>
    <div class="text-center" style="padding: 6px 15px;">
        <button class="btn btn-sm btn-default" style="width:100%; background-color: #ededed;" type="submit" id="searchEst">Go!</button>
    </div>

</form>
<div class="clear"></div>
<script>
    $(document).ready(function () {
        let tags = <?= isset($select2Tags) ? $select2Tags : json_encode([]); ?>;
        let estimators = <?= isset($select2Estimators) ? $select2Estimators : json_encode([]); ?>;
        let crews = <?= isset($select2Crews) ? $select2Crews : json_encode([]); ?>;
        let services = <?= isset($select2Services) ? $select2Services : json_encode([]); ?>;
        let products = <?= isset($select2Products) ? $select2Products : json_encode([]); ?>;
        let bundles = <?= isset($select2Bundles) ? $select2Bundles : json_encode([]); ?>;
        let data = <?= isset($select2Tags)?$select2Tags:json_encode([]); ?>;

        Common.init_select2([
            {
                'selector':'.js-tags-select2',
                options:{
                    'data': tags,
                    'tags': true,
                    'placeholder': 'Tags',
                    'separator': '|'
                }
            }
        ]);
        Common.init_select2([
            {
                'selector': '.js-estimators-select2',
                options: {
                    'data': [{
                        'text':"Active",
                        "children": estimators.active
                    },{
                        'text':"Inactive",
                        "children": estimators.inactive
                    }],
                    'tags': true,
                    'placeholder': 'Estimators',
                    'separator': '|'
                }
            }
        ]);
        Common.init_select2([
            {
                'selector': '.js-crews-select2',
                options: {
                    'data': crews,
                    'tags': true,
                    'placeholder': 'Specialists',
                    'separator': '|'
                }
            }
        ]);
        Common.init_select2([
            {
                'selector': '.js-services-select2',
                options: {
                    'data': services,
                    'tags': true,
                    'placeholder': 'Services',
                    'separator': '|'
                }
            }
        ]);
        Common.init_select2([
            {
                'selector': '.js-products-select2',
                options: {
                    'data': products,
                    'tags': true,
                    'placeholder': 'Products',
                    'separator': '|'
                }
            }
        ]);
        Common.init_select2([
            {
                'selector': '.js-bundles-select2',
                options: {
                    'data': bundles,
                    'tags': true,
                    'placeholder': 'Bundles',
                    'separator': '|'
                }
            }
        ]);
        $(document).ready(function () {
            $('.js-crews-select2').on('select2-open', function (e)  //$('.js-crews-select2 .select2-input').on('click',function()
            {
                $('.select2-drop-active .select2-results li').removeClass('select2-disabled');
                $('.select2-drop-active .select2-results li').each(function()
                {
                    if($(this).hasClass('select2-selected')) {
                        $(this).removeClass('select2-selected');
                    }
                });
            });
            $('.js-crews-select2').on('select2-change', function (e) {
                var data = e.params.data;
            });
            $('.items-filter').on("select2-close", function(e) {
                var estimators = $('.js-estimators-select2').select2("data");
                var crews = $('.js-crews-select2').select2("data");
                var services = $('.js-services-select2').select2("data");
                var products = $('.js-products-select2').select2("data");
                var bundles = $('.js-bundles-select2').select2("data");
            });
            $(document).on('click', '.filter-container .dropdown-menu', function (e) {
                e.stopPropagation();
            });
            $(document).on('click', '#wo_status a', function() {
                var form = $('#wo-search-form');
                status_id = $(this).attr('data-statusname');
                $(form).find('[name="wo_status_id"]').find('option:selected').removeAttr('selected');
                $(form).find('[name="wo_status_id"]').val(status_id);
                $(form).find('[name="wo_status_id"] option[value="'+ status_id +'"]').attr('selected', 'selected');
                $(form).submit();
            });
            $('#wo-search-form').on('submit', function(e) {
                e.preventDefault();
                let sendData = new FormData(e.target);
                var data = new Array();
                $.each(Object.fromEntries(sendData), function (key, value) {
                    var name = key;
                    if(!value.length)
                        return;
                    //console.log(Object.fromEntries(sendData));
                    if(key == 'wo_status_id')
                        data[name] = value;
                    else {
                        data[name] = [];

                        $.each(value.split('|'), function (jkey, jval) {
                            data[name].push(jval);
                        });
                    }
                });

                window.clientFilter = data;
                dataTable.ajax.reload();
            })
        });

    });
</script>