<?php if ($this->session->userdata('user_type') == "admin" || is_cl_permission_owner() || is_cl_permission_all()) : ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/modules/leads/estimators_dropdown.css'); ?>?v=1.41">

    <a class="btn btn-xs btn-danger btn-rounded day-off-btn" title="Lead map filter">
        <i class="fa fa-filter"></i>
    </a>
    <aside class="filterForm aside-lg b-l pos-abt bg-light b-a day-off-container" style="display: none;">

    <style type="text/css">
        .lead-map-filer-block .dropdown h5, .panel .panel-body .dropdown h5 {
            padding: 0 15px;
        }

        .leads-map-list-group-items .select2-container {
            margin-top: 5px;
        }

        .lead-map-filer-block .list-group h5 {
            margin-bottom: 5px;
        }

        .leads-map-list-group-items .priorityBlock {
            padding-top: 0;
        }

        .leads-map-list-group-items .list-group-item--switch {
            display: flex;
            flex-direction: row-reverse;
            justify-content: space-between;
            align-items: center;
            height: 50px;
        }

        .leads-map-list-group-items .list-group-item--switch .switch-mini {
            margin: 0;
            display: flex;
        }

        .leads-map-list-group-items .list-group-item--assign {
            display: flex;
            flex-direction: row-reverse;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f1f1;
            margin-top: 10px;
        }

        .leads-map-list-group-items .list-group-item--assign .switch-mini {
            margin: 0;
            display: flex;
        }

        .infowindow-container .dropdown-menu .change-lead-priority a {
            text-decoration: none!important;
        }

        .infowindow-container .dropdown-menu {
            padding: 0;
        }

        .leads-map-list-group-items .userBlock .showUsers {
            margin-top: 5px!important;
        }

        .leads-map-list-group-items .userBlock {
            padding-top: 0;
        }

        .filterForm .padder .panel {
            margin-bottom: 10px!important;
        }

        .show-on--body {
            padding: 0 15px;
        }

        .show-on--body .leads-map-list-group-items {
            margin-bottom: 0;
        }

        .body--list-group {
            margin-bottom: 0;
        }

        .body--list-group .leads-map-list-group-items {
            border-top: 1px solid #f1f1f1;
            border-bottom: 1px solid #f1f1f1;
            margin: 15px 0;
        }

        .list-group-item--label {
            display: flex;
            align-items: center;
        }

        .list-group-item--label i{
            margin-right: 5px;
        }
    </style>
    
        <h5 class="text-center font-bold">Show on map</h5>
        <div class="padder p-sides-10 scrollable" style="height: 95%;">
            <section class="panel panel-default pos-rlt clearfix" style="border: 1px solid #cfcfcf">
                <div class="panel-body clearfix show-on--body">
                    <div class="dropdown">
                        <div class="list-group bg-white body--list-group">
                            <ul class="leads-map-list-group-items">
                                <li class="list-group-item list-group-item--switch">
                                    <span class="pull-right">
                                        <label class="switch-mini">
                                            <input type="checkbox" data-toggle="toggle" class="agent showLeads" value="0" checked />
                                            <span></span>
                                        </label>
                                    </span>
                                    <div class="list-group-item--label">
                                        <i class="icon-muted">
                                            <img height="30" src="<?php echo mappin_svg('#00E64D', 'N', false, '#000'); ?>"/>
                                        </i> Leads
                                    </div>
                                </li>
                                <li class="list-group-item list-group-item--switch">
                                    <span class="pull-right">
                                        <label class="switch-mini">
                                            <input type="checkbox" data-toggle="toggle" class="agent showTasks" value="0" checked />
                                            <span></span>
                                        </label>
                                    </span>
                                    <div class="list-group-item--label">
                                        <i class="icon-muted">
                                            <img height="30" src="<?php echo mappin_svg('#ffffff', 'T', false, '#000'); ?>"/>
                                        </i> Tasks
                                    </div>
                                </li>
                                <?php if(config_item('gps_enabled')) : ?>
                                <li class="list-group-item list-group-item--switch">
                                    <span class="pull-right">
                                        <label class="switch-mini">
                                            <input type="checkbox" data-toggle="toggle" class="agent showVehicles" value="0" checked />
                                            <span></span>
                                        </label>
                                    </span>
                                    <div class="list-group-item--label">
                                        <i class="icon-muted">
                                            <img height="30" src="<?php echo base_url(); ?>uploads/trackericon/cam_bleue.png"/>
                                        </i> Vehicles
                                    </div>
                                </li>
                                <?php endif; ?>
                                <li class="list-group-item list-group-item--switch">
                                    <span class="pull-right">
                                        <label class="switch-mini" id="toggle_gps">
                                            <input type="checkbox" data-toggle="toggle" class="agent gps-tracker-checkbox" checked />
                                            <span></span>
                                        </label>
                                    </span>
                                    <div class="list-group-item--label">
                                        <i class="icon-muted glyphicon glyphicon-globe gps-icon text-info"></i>
                                        GPS Tracking
                                    </div>
                                </li>

                            </ul>

                        </div>
                    </div>
                </div>
            </section>
            <h5 class="text-center font-bold lead-map-filer-block">Lead filters</h5>
            <section class="panel panel-default pos-rlt clearfix lead-map-filer-block" style="border: 1px solid #cfcfcf">
                <div class="panel-body clearfix">
                    <div class="dropdown">
                        <div class="list-group bg-white">

                            <h5>Lead Priority</h5>

                            <ul class="leads-map-list-group-items">

                                <li class="list-group-item priorityBlock">
                                    <!--<span class="h6"><i class="fa fa-user fa-1x icon-muted"></i> Users</span>-->
                                    <input type="hidden" name="proprityInput" autocomplete="false"
                                           value="<?= 'Emergency|Priority|Regular' ?>"
                                           data-value=""
                                           class="showPriorities priorities w-100" data-toggle="tooltip" data-placement="top"
                                           title="" data-original-title="" placeholder="Any priority" />
                                </li>

                            </ul>
                            <h5><i class="fa fa-user fa-1x icon-muted"></i>&nbsp;Assigned to</h5>
                            <ul class="leads-map-list-group-items">
                                <li class="list-group-item userBlock">
                                    <!-- <span class="h6">Users</span> -->
                                    <input type="hidden" name="users" autocomplete="false"
                                           value="<?= implode('|', array_column($select2Users, 'id')); ?>"
                                           data-value=""
                                           class="showUsers users w-100" data-toggle="tooltip" data-placement="top"
                                           title="" data-original-title="" placeholder="All users" />
                                </li>
                                <li class="list-group-item list-group-item--assign">
                                    <span class="pull-right">
                                        <label class="switch-mini">
                                            <input type="checkbox" data-toggle="toggle" class="agent showPoint"
                                                   id="showPointPriorityNotAssigned"
                                                   value="0" />
                                            <span></span>
                                        </label>
                                    </span>
                                    <div class="list-group-item--label">
                                        <i class="icon-muted">
                                            <img height="30" src="<?php echo mappin_svg('#00E64D', '&#9899;', false, '#000'); ?>"/>
                                        </i> Not Assigned
                                    </div>
                                </li>
                            </ul>

                            <ul  class="leads-map-list-group-items">
                                <li class="list-group-item">
                                    <span class="h6"><i class="fa fa-wrench fa-1x icon-muted"></i> Services</span>
                                    <input type="hidden" name="est_services" autocomplete="false"
                                           class="showLeadService est_services w-100"
                                           data-toggle="tooltip" data-placement="top" title="" data-original-title=""/>
                                </li>

                                <li class="list-group-item">
                                    <span class="h6"><i class="fa fa-shopping-cart fa-1x icon-muted"></i> Products</span>
                                    <input type="hidden" name="est_products" autocomplete="false"
                                           class="showLeadProduct est_products w-100"
                                           data-toggle="tooltip" data-placement="top" title="" data-original-title=""/>
                                </li>

                                <li class="list-group-item">
                                    <span class="h6"><i class="fa fa-gift fa-1x icon-muted"></i> Bundles</span>
                                    <input type="hidden" name="est_bundles" autocomplete="false"
                                           class="showLeadBundle est_bundles w-100"
                                           data-toggle="tooltip" data-placement="top" title="" data-original-title=""/>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="dropdown dropup pull-left">

                    </div>
                </div>
            </section>

        </div>
    </aside>
    <script>
        let itemsForSelect2 = <?= getCategoriesItemsForSelect2() ?>;
        let selectTags = itemsForSelect2.services;
        let selectTagsProducts = itemsForSelect2.products;
        let selectTagsBundles = itemsForSelect2.bundles;
        let usersArrayState = [...<?= json_encode($select2Users) ?>, ];
        let selectUsers = <?= json_encode(json_encode($select2Users)) ?>;
        let selectPriorities = <?= json_encode(json_encode([
            ['id' => 'Emergency', 'text' => 'Emergency'],
            ['id' => 'Priority', 'text' => 'Priority'],
            ['id' => 'Regular', 'text' => 'Regular'],
        ])) ?>;

        $(document).ready(function () {

            var leadForms = $('.filterForm');

            initSelect2(leadForms.find("input.est_services"), selectTags, "Select Services");
            initSelect2(leadForms.find("input.est_products"), selectTagsProducts, "Select Products");
            initSelect2(leadForms.find("input.est_bundles"), selectTagsBundles, "Select Bundles");
            initSelect2(leadForms.find("input.users"), selectUsers, "Select Users");
            initSelect2(leadForms.find("input.priorities"), selectPriorities, "Select Priorities");

        });
    </script>
<?php endif; ?>
