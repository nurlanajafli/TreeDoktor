<div class="col-sm-7 p-n m-left-0" id="schedule-map" style="height: 100%;">

</div>
<div class="col-sm-5 p-n bg-white p-relative" style="height: 100%;" id="workordersCoverSection">

    <section id="search-workorders-block" class="aside-xl no-shadow search-fileld-dropdown hide" style="border:none">
        <section class="panel m-b-n" style="box-shadow: none!important;">
            <form role="search" name="search" id="searchSchedule" method="post" class="input-append" data-type="ajax" data-url="/schedule/workordersByStatuses" data-callback="ScheduleMapper.search_result" style="border: none">
                <div id="search-additional-filters" class="hidden"></div>
                <div class="form-group wrapper m-b-none p-10 p-right-15" style="">
                    <div class="input-group">
                        <input type="text" class="form-control no-shadow input-sm" placeholder="Search" name="search_keyword" value="">
                        <span class="input-group-btn">
                            <button type="button" id="search-workorders-reset-filter" class="btn btn-default btn-icon no-shadow btn-sm"><i class="fa fa-times"></i></button>
                            <button type="button" id="search-workorders-reset" class="btn btn-default btn-icon no-shadow btn-sm" data-toggle="class:hide animated fadeInLeft,hide,active,hide" data-target="#search-workorders-block,.eventDate,#woSearchShow,#search-workorders-reset-filter"><i class="fa fa-times"></i></button>
                            <button type="submit" class="btn btn-info btn-icon no-shadow btn-sm"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                </div>
            </form>
            <div class="clear"></div>
        </section>
    </section>

    <div class="row p-top-10 hidden-sm"></div>

    <div class="row" style="min-height: 55px;">
        <div class="col-lg-7 col-md-7 hidden-sm p-n">
            <div class="btn-group refresh-filter-block pull-left" data-toggle="buttons">
                <a href="#" class="pull-left hidden-sm btn-xs text-warning warning-hover xs-width" title="Refresh Workorders" id="reloadWorkorders"  style="font-size: 18px">
                    <i class="fa fa-refresh"></i>
                </a>
            </div>

            <div class="eventDate pull-left" id="event-date-view"></div>

            <div class="clear"></div>
        </div>
        <div class="col-lg-5 col-md-5 col-sm-12 p-relative dropdown-sm-flex">

            <div id="workorders-statuses-dropdown" class="workorders-statuses-dropdown order-sm-first p-right-15">

            </div>

        </div>
    </div>

    <div id="scheduleWorkordersScrollBlock" style="padding: 0px;overflow-y: auto;position: absolute;bottom: 55px;width: 100%;overflow-x: hidden;">
        <div class="m-b-sm filters p-top-10" style="display: none;" id="workorders-filters-block"></div>

        <div class="tabbable m-t-xs">
            <div class="panel-group" id="scheduleWorkorders" role="tablist" aria-multiselectable="false"></div>
        </div>
    </div>

    <div class="bg-light" style="height: 55px;position: absolute;bottom: 0;left: 0;right: 0;">
        <!-- Old search  -->
        <div class="btn-group m-r dropup pull-right crewsSelect" style="display: none;margin-right: 180px;margin-top: 12px;height: 32px;" id="crewsSelect">
            <button data-toggle="dropdown" class="btn btn-sm btn-default dropdown-toggle no-shadow" style="height: 32px;">
                <span class="dropdown-label" style="display: inline-block;"></span>
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-select" style="max-height: 250px;min-width: 220px;overflow-y: scroll;">

            </ul>
        </div>
    </div>

</div>

<div id="workorders-statuses-dropdown-body" class="hide">
    <section>
    </section>
    <button class="btn btn-primary btn-sm change-wo-status" data-toggle="class:hide" data-target="#workorders-statuses-dropdown-body" type="button" style="position: absolute; bottom: 13px;left: 0; right: 0; border-radius: 0;z-index: 100">GO!</button>
</div>

<div id="map-workorder-infowindow" class="hidden"></div>
<script type="text/x-jsrender" id="map-workorder-infowindow-tmp">
<?php $this->load->view('modals/event_marker_infowindow'); ?>
</script>

<script type="text/x-jsrender" id="workorders-cover-tmp">
<?php $this->load->view('modals/partials/workorders_cover_section'); ?>
</script>

<script type="text/x-jsrender" id="workorders-cover-tmp-empty">
    <p class="text-muted h4 b-b text-center p-10">No data found</p>
</script>

<script type="text/x-jsrender" id="event-date-tmp">
    <?php $this->load->view('modals/partials/event_dates'); ?>
</script>

<script type="text/x-jsrender" id="workorders-statuses-dropdown-tmp">
    <?php $this->load->view('modals/partials/workorders_statuses_dropdown'); ?>
</script>

<script type="text/x-jsrender" id="workorders-statuses-dropdown-body-tmp">
    <?php $this->load->view('modals/partials/workorders_statuses_dropdown_body'); ?>
</script>

<script type="text/x-jsrender" id="modal-workorder-details-tmp">
    <?php $this->load->view('modals/partials/modal_workorder_details'); ?>
</script>

<script type="text/x-jsrender" id="modal-estimate-files-tmp">
    <?php $this->load->view('modals/partials/estimate_files'); ?>
</script>

<script type="text/x-jsrender" id="status-logs-tmp">
    <?php $this->load->view('modals/partials/statuses_log'); ?>
</script>

<script type="text/x-jsrender" id="workorders-filters-block-tmp">
<?php $this->load->view('modals/partials/workorders_filters'); ?>
</script>

<script type="text/x-jsrender" id="filter-wo_status_id-tmp">
    {{if active_status.length}}
        {{props active_status}}
            <input type="hidden" name="wo_status_id[]" value="{{:prop}}">
        {{/props}}
    {{/if}}
    <input type="hidden" name="search_keyword" value="{{:search_keyword}}">
</script>

<script type="text/x-jsrender" id="filter-selected-estimators-tmp">
    {{if filter_estimator !=undefined && filter_estimator.length}}
        {{for filter_estimator itemVar="~id"}}
            {{if ~getEstimatorById(~id)}}
            <label class="label label-default h5 font-10 delete-filter-item" data-model="filter_estimator" data-index="{{:#getIndex()}}" style="background:{{:~getEstimatorById(~id).color}}">{{:~getEstimatorById(~id).full_name}} <i class="fa fa-times"></i></label>
            <input type="hidden" name="filter_estimator[]" value="{{:~id}}">
            {{/if}}
        {{/for}}
    {{else}}
        <p class="text-center m-n font-10"><small class="font-10"><i>No selected filters</i> <i class="fa fa-check text-success"></i></small></p>
    {{/if}}
</script>

<?php /*
<script type="text/x-jsrender" id="filter-selected-equipment-tmp">
    {{if filter_equipment !=undefined && filter_equipment.length}}
        {{for filter_equipment itemVar="~id"}}
            {{if ~getEquipmentById(~id)}}
            <label class="label label-default h5 font-10 delete-filter-item" data-model="filter_equipment" data-index="{{:#getIndex()}}" style="background:{{:~getEquipmentById(~id).group.group_color}}">{{:~getEquipmentById(~id).eq_name}} <i class="fa fa-times"></i></label>
            <input type="hidden" name="filter_equipment[]" value="{{:~id}}">
            {{/if}}
        {{/for}}
    {{else}}
        <p class="text-center m-n font-10"><small class="font-10"><i>No selected filters</i> <i class="fa fa-check text-success"></i></small></p>
    {{/if}}
</script>
*/ ?>

<script type="text/x-jsrender" id="filter-selected-crews-tmp">
    {{if filter_crew !=undefined && filter_crew.length}}
        {{for filter_crew itemVar="~crew_id"}}
            {{if ~getCrewById(~crew_id)}}
            <label class="label label-default h5 font-10 delete-filter-item" data-model="filter_crew" style="background:{{:~getCrewById(~crew_id).crew_color}}" data-index="{{:#getIndex()}}">{{:~getCrewById(~crew_id).crew_name}} <i class="fa fa-times"></i></label>
            <input type="hidden" name="filter_crew[]" value="{{:~crew_id}}">
            {{/if}}
        {{/for}}
    {{else}}
        <p class="text-center m-n font-10"><small class="font-10"><i>No selected filters</i> <i class="fa fa-check text-success"></i></small></p>
    {{/if}}
</script>

<script type="text/x-jsrender" id="filter-selected-services-tmp">
    {{if filter_service !=undefined && filter_service.length}}
        {{for filter_service itemVar="~service_id"}}
            {{if ~getServiceById(~service_id)}}
            <label class="label label-info h5 font-10 delete-filter-item" data-model="filter_service" data-index="{{:#getIndex()}}">{{:~getServiceById(~service_id).service_name}} <i class="fa fa-times"></i></label>
            <input type="hidden" name="filter_service[]" value="{{:~service_id}}">
            {{/if}}
        {{/for}}
    {{else}}
        <p class="text-center m-n font-10"><small class="font-10"><i>No selected filters</i> <i class="fa fa-check text-success"></i></small></p>
    {{/if}}
</script>

<script type="text/x-jsrender" id="filter-selected-products-tmp">
    {{if filter_product !=undefined && filter_product.length}}
        {{for filter_product itemVar="~service_id"}}
            {{if ~getServiceById(~service_id)}}
            <label class="label label-warning h5 font-10 delete-filter-item" data-model="filter_product" data-index="{{:#getIndex()}}">{{:~getServiceById(~service_id).service_name}} <i class="fa fa-times"></i></label>
            <input type="hidden" name="filter_product[]"  value="{{:~service_id}}">
            {{/if}}
        {{/for}}
    {{else}}
        <p class="text-center m-n font-10"><small class="font-10"><i>No selected filters</i> <i class="fa fa-check text-success"></i></small></p>
    {{/if}}
</script>

<script type="text/x-jsrender" id="filter-selected-bundles-tmp">
    {{if filter_bundle !=undefined && filter_bundle.length}}
        {{for filter_bundle itemVar="~service_id"}}
            {{if ~getServiceById(~service_id)}}
            <label class="label label-primary h5 font-10 delete-filter-item" data-model="filter_bundle" data-index="{{:#getIndex()}}">{{:~getServiceById(~service_id).service_name}} <i class="fa fa-times"></i></label>
            <input type="hidden" name="filter_bundle[]" value="{{:~service_id}}">
            {{/if}}
        {{/for}}
    {{else}}
        <p class="text-center m-n font-10"><small class="font-10"><i>No selected filters</i> <i class="fa fa-check text-success"></i></small></p>
    {{/if}}
</script>

<script type="text/x-jsrender" id="filter-selected-estimates-services-status-tmp">
    {{if filter_estimates_services_status !=undefined && filter_estimates_services_status.length}}
        {{for filter_estimates_services_status itemVar="~status_id"}}
            {{if ~getStatusById(~status_id)}}
            <label class="label label-primary h5 font-10 delete-filter-item" data-model="filter_estimates_services_status" data-index="{{:#getIndex()}}">{{:~getStatusById(~status_id).services_status_name}} <i class="fa fa-times"></i></label>
            <input type="hidden" name="filter_estimates_services_status[]" value="{{:~status_id}}">
            {{/if}}
        {{/for}}
    {{else}}
        <p class="text-center m-n font-10"><small class="font-10"><i>No selected filters</i> <i class="fa fa-check text-success"></i></small></p>
    {{/if}}
</script>

<script type="text/x-jsrender" id="filters-header-tmp">
    <span class="label bg-success">{{:countWorkorders}}</span> Results
    {{if disabled==undefined}}
    <a class="pull-right cursor-pointer" id="reset-all-map-filters">Clear <i class="fa fa-times"></i></a>
    {{/if}}
    <div class="clear"></div>
</script>

<script type="text/x-jsrender" id="search-additional-filters-tmp">

    {{if filter_estimator !=undefined && filter_estimator.length}}
        {{for filter_estimator itemVar="~id"}}
            {{if ~getEstimatorById(~id)}}
            <input type="hidden" name="filter_estimator[]" value="{{:~id}}">
            {{/if}}
        {{/for}}
    {{/if}}
    <?php /*
    {{if filter_equipment !=undefined && filter_equipment.length}}
        {{for filter_equipment itemVar="~id"}}
            {{if ~getEquipmentById(~id)}}
            <input type="hidden" name="filter_equipment[]" value="{{:~id}}">
            {{/if}}
        {{/for}}
    {{/if}}
    */ ?>
    {{if filter_crew !=undefined && filter_crew.length}}
        {{for filter_crew itemVar="~crew_id"}}
            {{if ~getCrewById(~crew_id)}}
            <input type="hidden" name="filter_crew[]" value="{{:~crew_id}}">
            {{/if}}
        {{/for}}
    {{/if}}

    {{if filter_service !=undefined && filter_service.length}}
        {{for filter_service itemVar="~service_id"}}
            {{if ~getServiceById(~service_id)}}
            <input type="hidden" name="filter_service[]" value="{{:~service_id}}">
            {{/if}}
        {{/for}}
    {{/if}}

    {{if filter_product !=undefined && filter_product.length}}
        {{for filter_product itemVar="~service_id"}}
            {{if ~getServiceById(~service_id)}}
            <input type="hidden" name="filter_product[]"  value="{{:~service_id}}">
            {{/if}}
        {{/for}}
    {{/if}}

    {{if filter_bundle !=undefined && filter_bundle.length}}
        {{for filter_bundle itemVar="~service_id"}}
            {{if ~getServiceById(~service_id)}}
            <input type="hidden" name="filter_bundle[]" value="{{:~service_id}}">
            {{/if}}
        {{/for}}
    {{/if}}

    {{if filter_estimates_services_status !=undefined && filter_estimates_services_status.length}}
        {{for filter_estimates_services_status itemVar="~status_id"}}
            {{if ~getStatusById(~status_id)}}
            <input type="hidden" name="filter_estimates_services_status[]" value="{{:~status_id}}">
            {{/if}}
        {{/for}}
    {{/if}}

    {{if active_status.length}}
        {{props active_status}}
            <input type="hidden" name="wo_status_id[]" value="{{:prop}}">
        {{/props}}
    {{/if}}
</script>

<?php $this->load->view('clients/notes/notes_tmp'); ?>

<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/schedule/schedule_map.css'); ?>?v=<?php echo config_item('schedule_map.css'); ?>"/>

