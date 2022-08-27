<?php
/**
 * @var Equipment $eq
 */

use application\modules\equipment\models\Equipment;

?>
<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/equipment/equipment.css'); ?>"
      type="text/css"/>
<style>
    #settings {
        margin-top: 34px;
    }

    #settings .scrollable {
        overflow-x: auto;
    }

    #settings > header {
        padding-right: 0px;
        padding-left: 0px;
    }

    #settings > header > .nav {
        display: inline-block;
        padding-left: 25px;
        padding-right: 25px;
    }

    #settings .tab-pane > section {
        display: inline-table;
    }
</style>
<ul class="breadcrumb no-border no-radius b-b b-light">
    <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
    <li class="active">Equipment Settings</li>
</ul>
<section id="settings" class="vbox" diez-app="EquipmentSettingsApp" diez-src="equipment/components/settings.js">
    <section class="col-sm-12 panel panel-default p-n vbox">
        <header class="header hbox bg-light bg-gradient">
            <ul class="nav nav-tabs nav-white m-l-none m-r-none">
                <li class="nav-item">
                    <a class="nav-link" href="#service-types" data-toggle="tab">Service Types</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#repair-statuses" data-toggle="tab">Repair Statuses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#repair-types" data-toggle="tab">Repair Types</a>
                </li>
                <li class='nav-item dropdown collapsed-menu'>
                    <a class="nav-link dropdown-toggle" data-toggle='dropdown' href='#' role='button'
                       aria-haspopup="true" aria-expanded="false">
                        <span class="glyphicon glyphicon-chevron-right"></span>
                    </a>
                    <ul class="dropdown-menu collapsed-tabs text-left" aria-labelledby="dropdownMenuButton">
                    </ul>
                </li>
            </ul>
        </header>
        <section class="scrollable">
            <div class="tab-content">
                <div class="tab-pane" id="service-types">
                    <?php $this->load->view('equipment/partials/settings_tab_service_types'); ?>
                </div>
                <div class="tab-pane" id="repair-statuses">
                    <?php $this->load->view('equipment/partials/settings_tab_repair_statuses'); ?>
                </div>
                <div class="tab-pane" id="repair-types">
                    <?php $this->load->view('equipment/partials/settings_tab_repair_types'); ?>
                </div>
            </div>
        </section>
    </section>
</section>
<script>
    var isAdmin = <?php echo isAdmin() ? 'true' : 'false' ?>;
    var DATE_FORMAT = "<?php echo getJSDateFormat(); ?>";
</script>

<?php $this->load->view('includes/footer'); ?>

    
