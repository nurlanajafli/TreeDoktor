    <div class="wait-loading" style="position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    left: 0;
    background: #ffffff87;
    display: none;
    z-index: 9;"></div>

    <form data-type="ajax" data-url="/schedule/workordersByStatuses" id="map-workorders-filter-form" data-callback="ScheduleMapper.update_workorders_list" data-global="false">
        <input type="hidden" {{if disabled!=undefined}}disabled="disabled"{{/if}} name="count_estimators" value="{{:~object_length(estimators)}}">
        <input type="hidden" {{if disabled!=undefined}}disabled="disabled"{{/if}} name="count_crews" value="{{:~object_length(crews)}}">
        <input type="hidden" {{if disabled!=undefined}}disabled="disabled"{{/if}} name="count_services" value="{{:~object_length(services)}}">
        {{if equipment!=undefined}}
        <input type="hidden" {{if disabled!=undefined}}disabled="disabled"{{/if}} name="count_equipment" value="{{:~object_length(equipment)}}">
        {{/if}}
        <div class="filter-item-view" id="filter-wo_status_id"></div>

        <section class="panel panel-default portlet-item" style="opacity: 1;">
            <header class="panel-heading" id="filters-header" style="background: #f0fbff;"></header>
            <section class="panel-body">
                <article class="media m-top-10">
                    <div class="media-body" style="margin-top: -5px;">
                        <div class="row">
                            <div class="col-md-5 p-top-5">
                                <a href="#" class="h6"><i class="fa fa-user fa-1x icon-muted text-warning"></i> Estimators</a>
                                <small class="block">
                                    <select {{if disabled!=undefined}}disabled="disabled"{{/if}} class="items-filter form-control input-sm" data-model="filter_estimator" style="width: 100%">
                                        <option value="0"></option>
                                        {{if ~object_length(estimators)}}
                                        {{props estimators}}
                                        <option value="{{:prop.id}}">{{:prop.full_name}}</option>
                                        {{/props}}
                                        {{/if}}
                                    </select>
                                </small>
                            </div>
                            <div class="col-md-7">
                                {{if disabled==undefined}}
                                <a class="pull-right items-filter-clear p-right-5 p-left-5" data-model="filter_estimator"><small><i class="fa fa-times"></i></small></a>
                                {{/if}}
                                <div class="clear"></div>
                                <div class="filter-item-view" id="filter-selected-estimators"></div>
                            </div>
                        </div>
                    </div>
                </article>
                <div class="line pull-in"></div>
                <?php /*
                <article class="media m-top-10">
                    <div class="media-body" style="margin-top: -5px;">
                        <div class="row">
                            <div class="col-md-5 p-top-5">
                                <a href="#" class="h6"><i class="fa fa-truck fa-1x icon-muted text-warning"></i> Equipment:</a>
                                <small class="block">
                                    <select class="items-filter form-control input-sm" data-model="filter_equipment" style="width: 100%">
                                        <option value="0"></option>
                                        {{if ~object_length(equipment)}}
                                        {{props equipment}}
                                        <option value="{{:prop.eq_id}}">{{:prop.eq_name}}</option>
                                        {{/props}}
                                        {{/if}}
                                    </select>
                                </small>
                            </div>
                            <div class="col-md-7">
                                <a class="pull-right items-filter-clear p-right-5 p-left-5" data-model="filter_equipment"><small><i class="fa fa-times"></i></small></a>
                                <div class="clear"></div>
                                <div class="filter-item-view" id="filter-selected-equipment"></div>
                            </div>
                        </div>
                    </div>
                </article>
                <div class="line pull-in"></div>
                */ ?>
                <article class="media m-top-10">
                    <div class="media-body" style="margin-top: -5px;">
                        <div class="row">
                            <div class="col-md-5 p-top-5">
                                <a href="#" class="h6"><i class="fa fa-group fa-1x icon-muted text-warning"></i> Specialists:</a>
                                <small class="block">

                                    <select {{if disabled!=undefined}}disabled="disabled"{{/if}} class="items-filter form-control input-sm" style="width: 100%" data-model="filter_crew">
                                        <option value="0"></option>
                                        {{if ~object_length(crews)}}
                                        {{props crews}}
                                        <option value="{{:prop.crew_id}}">{{:prop.crew_name}}</option>
                                        {{/props}}
                                        {{/if}}
                                    </select>

                                </small>
                            </div>
                            <div class="col-md-7">
                                {{if disabled==undefined}}
                                <a class="pull-right items-filter-clear p-right-5 p-left-5" data-model="filter_crew">
                                    <small><i class="fa fa-times"></i></small>
                                </a>
                                {{/if}}
                                <div class="clear"></div>
                                <div class="filter-item-view" id="filter-selected-crews"></div>
                            </div>
                        </div>
                    </div>
                </article>

                {{if ~filterServices(services).length}}
                <div class="line pull-in"></div>
                <article class="media m-top-10">
                    <div class="media-body" style="margin-top: -5px;">
                        <div class="row">
                            <div class="col-md-5 p-top-5">
                                <a href="#" class="h6"><i class="fa fa-wrench fa-1x icon-muted text-warning"></i> Services:</a>
                                <small class="block">

                                    <select {{if disabled!=undefined}}disabled="disabled"{{/if}} class="items-filter form-control input-sm" data-model="filter_service" style="width: 100%">
                                        <option value="0"></option>
                                        {{for ~filterServices(services)}}
                                        <option value="{{:service_id}}">{{:service_name}}</option>
                                        {{/for}}
                                    </select>

                                </small>
                            </div>
                            <div class="col-md-7">
                                {{if disabled==undefined}}
                                <a class="pull-right items-filter-clear p-right-5 p-left-5" data-model="filter_service"><small><i class="fa fa-times"></i></small></a>
                                {{/if}}
                                <div class="clear"></div>
                                <div class="filter-item-view" id="filter-selected-services"></div>
                            </div>
                        </div>
                    </div>
                </article>
                {{/if}}

                {{if ~filterProducts(services).length}}
                <div class="line pull-in"></div>
                <article class="media m-top-10">
                    <div class="media-body" style="margin-top: -5px;">
                        <div class="row">
                            <div class="col-md-5 p-top-5">
                                <a href="#" class="h6"><i class="fa fa-shopping-cart fa-1x icon-muted text-warning"></i> Products:</a>
                                <small class="block">

                                    <select {{if disabled!=undefined}}disabled="disabled"{{/if}} class="items-filter form-control input-sm" data-model="filter_product" style="width: 100%">
                                        <option value="0"></option>
                                        {{for ~filterProducts(services)}}
                                        <option value="{{:service_id}}">{{:service_name}}</option>
                                        {{/for}}
                                    </select>
                                </small>
                            </div>
                            <div class="col-md-7">
                                {{if disabled==undefined}}
                                <a class="pull-right items-filter-clear p-right-5 p-left-5" data-model="filter_product"><small><i class="fa fa-times"></i></small></a>
                                {{/if}}
                                <div class="clear"></div>
                                <div class="filter-item-view" id="filter-selected-products"></div>
                            </div>
                        </div>
                    </div>
                </article>
                {{/if}}

                {{if ~filterBundles(services).length}}
                <div class="line pull-in"></div>
                <article class="media m-top-10">
                    <div class="media-body" style="margin-top: -5px;">
                        <div class="row">
                            <div class="col-md-5 p-top-5">
                                <a href="#" class="h6"><i class="fa fa-gift fa-1x icon-muted text-warning"></i> Bundles:</a>
                                <small class="block">

                                    <select {{if disabled!=undefined}}disabled="disabled"{{/if}} class="items-filter form-control input-sm" data-model="filter_bundle" style="width: 100%">
                                        <option value="0"></option>
                                        {{for ~filterBundles(services)}}
                                        <option value="{{:service_id}}">{{:service_name}}</option>
                                        {{/for}}
                                    </select>

                                </small>
                            </div>
                            <div class="col-md-7">
                                {{if disabled==undefined}}
                                <a class="pull-right items-filter-clear p-right-5 p-left-5" data-model="filter_bundle"><small><i class="fa fa-times"></i></small></a>
                                {{/if}}
                                <div class="clear"></div>
                                <div class="filter-item-view" id="filter-selected-bundles"></div>
                            </div>
                        </div>
                    </div>
                </article>
                {{/if}}

                <div class="line pull-in"></div>
                <article class="media m-top-10">
                    <div class="media-body" style="margin-top: -5px;">
                        <div class="row">
                            <div class="col-md-5 p-top-5">
                                <a href="#" class="h6"><i class="fa fa-circle-o fa-1x icon-muted text-warning"></i> Estimate Service Status:</a>
                                <small class="block">

                                    <select {{if disabled!=undefined}}disabled="disabled"{{/if}} class="items-filter form-control input-sm" style="width: 100%" data-model="filter_estimates_services_status">
                                        <option value="-1"></option>
                                        {{if ~object_length(estimates_services_status)}}
                                        {{props estimates_services_status}}
                                        <option value="{{:prop.services_status_id}}">{{:prop.services_status_name}}</option>
                                        {{/props}}
                                        {{/if}}
                                    </select>

                                </small>
                            </div>
                            <div class="col-md-7">
                                {{if disabled==undefined}}
                                <a class="pull-right items-filter-clear p-right-5 p-left-5" data-model="filter_estimates_services_status">
                                    <small><i class="fa fa-times"></i></small>
                                </a>
                                {{/if}}
                                <div class="clear"></div>
                                <div class="filter-item-view" id="filter-selected-estimates-services-status"></div>
                            </div>
                        </div>
                    </div>
                </article>

            </section>
        </section>


    </form>
    <div class="clear"></div>
