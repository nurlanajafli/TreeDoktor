<?php
/**
 * @var Equipment $eq
 */

use application\modules\equipment\models\Equipment;

?>

<?php $this->load->view('includes/header'); ?>
<!--<link rel="stylesheet" href="--><?php //echo base_url('assets/css/colpick.css'); ?><!--"/>-->
<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/select2/select2.css'); ?>"
      type="text/css"/>
<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/select2/theme.css'); ?>" type="text/css"/>
<link rel="stylesheet" href="<?php echo base_url('assets/vendors/kartik-v/fileinput/css/fileinput.css'); ?>"
      type="text/css"/>
<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/equipment/equipment.css'); ?>"
      type="text/css"/>
<style>
    #equipment_item .thumb {
        cursor: pointer;
    }

    #equipment_item .thumb img {
        height: 64px;
        object-fit: contain;
        background-color: #78878a;
        cursor: pointer;
    }

    #equipment_item .thumb input {
        position: absolute;
        z-index: 2;
        top: 0;
        left: 0;
        filter: alpha(opacity=0);
        -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";
        opacity: 0;
        background-color: transparent;
        color: transparent;
        height: 100%;
    }

    #equipment_item .img-sold {
        width: 100%;
        position: absolute;
        top: 0px;
        left: 5px;
        z-index: 3;
        opacity: 0.4;
    }

    #equipment_item .counters a {
        color: #2e3e4e;
        text-decoration: none;
    }

    .switch {
        top: -10px;
    }

    .switch span {
        top: 10px;
    }

    .select2-container {
        width: 100%;
    }

    #profile > header {
        min-height: initial;
    }

    @media (min-width: 768px) {
        #profile > header ~ section {
            top: 34px;
        }
    }
</style>
<section id="profile" class="vbox">
    <header class="header bg-white b-b b-light">
        <ul class="breadcrumb no-border no-radius m-b-none pull-in">
            <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="<?php echo base_url('equipment'); ?>">Equipment</a></li>
            <?php if(!empty($eq->group)): ?>
            <li>
                <a href="<?php echo base_url('equipment/group/' . $eq->group->group_id); ?>"><?php echo $eq->group->group_name; ?></a>
            </li>
            <?php endif; ?>
            <li class="active"><?php echo $eq->eq_name; ?></li>
        </ul>
    </header>
    <section class="scrollable">
        <section class="hbox stretch">
            <aside id="aside_details" class="aside-lg bg-light lter b-r">
                <section id="equipment_item" class="vbox" diez-app="EquipmentProfileApp"
                         diez-src="equipment/components/profile.js"
                         data-equipment-id="<?php echo $eq->eq_id; ?>"
                         data-equipment-sold="<?php echo empty($eq->eq_sold_at) ? 0 : 1 ?>">
                    <section class="scrollable">
                        <div class="wrapper eq-details">
                        </div>
                        <div id="edit" class="modal fade" tabindex="-1" role="dialog"
                             aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <form method="POST" class="form-horizontal">
                                    <div class="modal-content panel panel-default p-n">
                                        <header class="panel-heading">
                                            <span>Edit</span>
                                            <button type="button" class="close" data-dismiss="modal">×</button>
                                        </header>
                                        <div class="modal-footer">
                                            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                                            <button class="btn btn-info" type="submit" style="30px"><span
                                                        class="btntext">Save</span></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </section>
                </section>

            </aside>
            <aside class="bg-white">
                <?php $this->load->view('equipment/partials/profile_tabs'); ?>
            </aside>
            <aside class="col-lg-4 b-l" id="aside_notes">
                <?php $this->load->view('equipment/partials/profile_notes'); ?>
            </aside>
        </section>
    </section>
</section>
<?php $this->load->view_hb('profile'); ?>
<?php $this->load->view_hb('profile_edit'); ?>
<?php $this->load->view_hb('sale'); ?>
<?php $this->load->view_hb('unsold'); ?>
<?php $this->load->view_hb('repair_create'); ?>
<?php $this->load->view_hb('form_multi_employee_row'); ?>
<?php $this->load->view_hb('form_multi_part_row'); ?>
<?php $this->load->view_hb('form_multi_file_row'); ?>
<script>
    var isAdmin = <?php echo isAdmin() ? 'true' : 'false' ?>;
    var equipmentData = <?php echo json_encode($eq); ?>;
    var currentUserData = <?php echo json_encode(request()->user()->load(['employee'])); ?>;
    var eqCounterTypes = <?php echo json_encode(Equipment::COUNTER_TYPES); ?>;
    var DATE_FORMAT = "<?php echo getJSDateFormat(); ?>";
</script>

<?php $this->load->view('includes/footer'); ?>

    
