<?php
/**
 * @var Equipment $eq
 */

use application\modules\equipment\models\Equipment;

?>
<style>
    #equipment_tabs .scrollable {
        overflow-x: auto;
    }

    #equipment_tabs > header {
        padding-right: 0px;
        padding-left: 0px;
    }

    #equipment_tabs > header > .nav {
        display: inline-block;
        padding-left: 25px;
        padding-right: 25px;
    }

    #equipment_tabs .tab-pane > section {
        display: inline-table;
    }
</style>
<section id="equipment_tabs" class="vbox" diez-app="EquipmentProfileTabsApp"
         diez-src="equipment/components/profile-tabs.js" data-equipment-id="<?php echo $eq->eq_id; ?>">
    <header class="header hbox bg-light bg-gradient">
        <section style="width: 35px">
            <a href="#aside_details" data-toggle="class:hide" class="btn btn-sm btn-default"><i
                        class="fa fa-caret-left text fa-lg"></i><i
                        class="fa fa-caret-right text-active fa-lg"></i></a>
        </section>
        <section>
            <ul class="nav nav-tabs nav-white m-l-none m-r-none">
                <li class="nav-item"><a class="nav-link" href="#services" data-toggle="tab">
                        <b class="badge bg-danger pull-right m-l-xs"><?php echo $eq->services_count; ?></b>Services</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="#repairs" data-toggle="tab">
                        <b class="badge bg-danger pull-right m-l-xs"><?php echo $eq->repairs_count; ?></b>Repairs</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="#parts" data-toggle="tab">Parts</a></li>

                <li class="nav-item"><a class="nav-link" href="#counters"
                                        data-toggle="tab"><?php echo ucfirst(DISTANCE_MEASUREMENT); ?>/Hrs
                        history</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="#files"
                                        data-toggle="tab">Files</a></li>
                <!--<li class="nav-item"><a class="nav-link" href="#reports" data-toggle="tab">Reports</a></li>
                <li class="nav-item"><a class="nav-link" href="#distance" data-toggle="tab">Distance
                        Report</a></li>-->
                <li class='nav-item dropdown collapsed-menu'>
                    <a class="nav-link dropdown-toggle" data-toggle='dropdown' href='#' role='button'
                       aria-haspopup="true" aria-expanded="false">
                        <span class="glyphicon glyphicon-chevron-right"></span>
                    </a>
                    <ul class="dropdown-menu collapsed-tabs text-left" aria-labelledby="dropdownMenuButton">
                    </ul>
                </li>
            </ul>
        </section>
        <section class="text-right" style="width: 35px">
            <a href="#aside_notes" data-toggle="class:hide" class="btn btn-sm btn-default"><i
                        class="fa fa-caret-right text fa-lg"></i><i
                        class="fa fa-caret-left text-active fa-lg"></i></a>
        </section>
    </header>
    <section class="scrollable">
        <div class="tab-content">
            <div class="tab-pane" id="services">
                <?php $this->load->view('equipment/partials/profile_tab_services'); ?>
                <?php $this->load->view('equipment/partials/profile_tab_service_reports'); ?>
            </div>
            <div class="tab-pane" id="repairs">
                <?php $this->load->view('equipment/partials/profile_tab_repairs'); ?>
            </div>
            <div class="tab-pane" id="parts">
                <?php $this->load->view('equipment/partials/profile_tab_parts'); ?>
            </div>
            <div class="tab-pane" id="counters">
                <?php $this->load->view('equipment/partials/profile_tab_counters'); ?>
            </div>
            <div class="tab-pane" id="files">
                <?php $this->load->view('equipment/partials/profile_tab_files'); ?>
            </div>
            <div class="tab-pane" id="interaction">
                <div class="text-center wrapper">
                    <i class="fa fa-spinner fa fa-spin fa fa-large"></i>
                </div>
            </div>
        </div>
    </section>
</section>