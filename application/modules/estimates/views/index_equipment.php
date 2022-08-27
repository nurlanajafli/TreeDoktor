<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/css/colpick.css'); ?>"/>

<script src="<?php echo base_url('assets/js/colpick.js'); ?>"></script>
<style type="text/css">
    .select2-display-none {
        display: none !important;
    }

    .select2-container-multi .select2-choices .select2-search-field {
        width: auto;
    }

    .select2-container-multi .select2-choices .select2-search-field input {
        width: 100% !important;
    }

    .select2-container-multi .select2-choices {
        min-height: 26px;
    }

    .select2-container {
        height: auto !important;
        z-index: 0 !important;
    }

    .select2-container a {
        height: 70% !important;
    }
</style>
<!-- All clients display -->
<section class="scrollable p-sides-15">
    <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
        <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
        <li class="active">Equipment</li>
    </ul>
    <section class="col-sm-12 panel panel-default p-n">
        <header class="panel-heading">Equipment
            <a href="#addEquipment" title="New Equipment" data-toggle="modal"
               class="btn btn-success btn-xs pull-right addEquipment" style="margin-top: -1px; margin-right: 10px;"
               type="button"><i class="fa fa-plus"></i></a>
        </header>
        <div class="col-md-4">
            <!-- Data display -->
            <header class="panel-heading"><strong>Vehicles</strong></header>
            <table class="table table-hover" id="vehicles-enabled">
                <thead>
                <tr>
                    <th>Equipment Name</th>
                    <th>Cost Per Hour</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($vehicles as $key => $val) : ?>
                    <tr class="enabled-equipment">
                        <td><?php echo $val->vehicle_name; ?></td>
                        <td><?php echo money($val->vehicle_per_hour_price); ?></td>
                        <td>
                            <a href="#edit<?php echo $val->vehicle_id; ?>" class="btn btn-default btn-xs" role="button"
                               data-toggle="modal" data-backdrop="static" data-keyboard="false"><i
                                        class="fa fa-pencil"></i></a>
                            <?php
                            if (isAdmin()) : ?>
                                <a href="#" data-id="<?php echo $val->vehicle_id; ?>" data-table="vehicles"
                                   data-enabled="disabled"
                                   class="btn btn-xs btn-danger group-delete"><i class="fa fa-ban icon-white"></i>
                                </a>

                            <?php endif; ?>
                            <?php $this->load->view('partials/equipment_modal', ['val' => $val]); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <header class="panel-heading"><strong>Attachments</strong></header>
            <table class="table table-hover" id="attach-enabled">
                <thead>
                <tr>
                    <th>Equipment Name</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($trailers as $key => $val) : ?>
                    <tr class="enabled-equipment">
                        <td><?php echo $val->vehicle_name; ?></td>
                        <td><?php echo money($val->vehicle_per_hour_price); ?></td>
                        <td>
                            <a href="#edit<?php echo $val->vehicle_id; ?>" class="btn btn-default btn-xs" role="button"
                               data-toggle="modal" data-backdrop="static" data-keyboard="false"><i
                                        class="fa fa-pencil"></i></a>
                            <?php
                            if (isAdmin()) : ?>
                                <a href="#" data-id="<?php echo $val->vehicle_id; ?>" data-table="attach"
                                   data-enabled="disabled"
                                   class="btn btn-xs btn-danger group-delete"><i class="fa fa-ban icon-white"></i>
                                </a>

                            <?php endif; ?>
                            <?php $this->load->view('partials/equipment_modal', ['val' => $val]); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <header class="panel-heading"><strong>Tools</strong></header>
            <table class="table table-hover" id="tools-enabled">
                <thead>
                <tr>
                    <th>Tool Name</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($tools as $key => $val) : ?>
                    <tr class="enabled-equipment">
                        <td><?php echo $val->vehicle_name; ?></td>
                        <td><?php echo money($val->vehicle_per_hour_price); ?></td>
                        <td>
                            <a href="#edit<?php echo $val->vehicle_id; ?>" class="btn btn-default btn-xs" role="button"
                               data-toggle="modal" data-backdrop="static" data-keyboard="false"><i
                                        class="fa fa-pencil"></i></a>
                            <?php
                            if (isAdmin()) : ?>
                                <a href="#" data-id="<?php echo $val->vehicle_id; ?>" data-table="tools"
                                   data-enabled="disabled"
                                   class="btn btn-xs btn-danger group-delete"><i class="fa fa-ban icon-white"></i>
                                </a>

                            <?php endif; ?>
                            <?php $this->load->view('partials/equipment_modal', ['val' => $val]); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-12 p-bottom-20">
            <div class="progress progress-sm m-t-xs m-b-none">
                <div class="progress-bar progress-bar-info" data-toggle="tooltip" style="width:100%"></div>
            </div>
        </div>
        <div class="col-md-4">
            <table class="table table-hover" id="vehicles-disabled">
                <tbody>

                <?php if (isset($disabled) && !empty($disabled)) : ?>
                    <?php foreach ($disabled as $key => $val) : ?>
                        <?php if ($val->vehicle_trailer === NULL) : ?>
                            <tr class="disabled-equipment">
                                <td><?php echo $val->vehicle_name; ?></td>
                                <td><?php echo money($val->vehicle_per_hour_price); ?></td>
                                <td>
                                    <a href="#edit<?php echo $val->vehicle_id; ?>" class="btn btn-default btn-xs"
                                       role="button"
                                       data-toggle="modal" data-backdrop="static" data-keyboard="false"><i
                                                class="fa fa-pencil"></i></a>
                                    <?php
                                    if (isAdmin()) : ?>
                                        <a href="#" data-id="<?php echo $val->vehicle_id; ?>" data-table="vehicles"
                                           data-enabled="enabled"
                                           class="btn btn-xs btn-info group-delete"><i class="fa fa-check"></i>
                                        </a>

                                    <?php endif; ?>
                                    <?php $this->load->view('partials/equipment_modal', ['val' => $val]); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <table class="table table-hover" id="attach-disabled">
                <tbody>
                <?php if (isset($disabled) && !empty($disabled)) : ?>
                    <?php foreach ($disabled as $key => $val) : ?>
                        <?php if ($val->vehicle_trailer == 1) : ?>
                            <tr class="disabled-equipment">
                                <td><?php echo $val->vehicle_name; ?></td>
                                <td><?php echo money($val->vehicle_per_hour_price); ?></td>
                                <td>
                                    <a href="#edit<?php echo $val->vehicle_id; ?>" class="btn btn-default btn-xs"
                                       role="button"
                                       data-toggle="modal" data-backdrop="static" data-keyboard="false"><i
                                                class="fa fa-pencil"></i></a>
                                    <?php
                                    if (isAdmin()) : ?>
                                        <a href="#" data-id="<?php echo $val->vehicle_id; ?>" data-table="attach"
                                           data-enabled="enabled"
                                           class="btn btn-xs btn-info group-delete"><i class="fa fa-check"></i>
                                        </a>

                                    <?php endif; ?>
                                    <?php $this->load->view('partials/equipment_modal', ['val' => $val]); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <table class="table table-hover" id="tools-disabled">
                <tbody>
                <?php if (isset($disabled) && !empty($disabled)) : ?>
                    <?php foreach ($disabled as $key => $val) : ?>
                        <?php if ($val->vehicle_trailer == 2) : ?>
                            <tr class="disabled-equipment">
                                <td><?php echo $val->vehicle_name; ?></td>
                                <td><?php echo money($val->vehicle_per_hour_price); ?></td>
                                <td>
                                    <a href="#edit<?php echo $val->vehicle_id; ?>" class="btn btn-default btn-xs"
                                       role="button"
                                       data-toggle="modal" data-backdrop="static" data-keyboard="false"><i
                                                class="fa fa-pencil"></i></a>
                                    <?php
                                    if (isAdmin()) : ?>
                                        <a href="#" data-id="<?php echo $val->vehicle_id; ?>" data-table="tools"
                                           data-enabled="enabled"
                                           class="btn btn-xs btn-info group-delete"><i class="fa fa-check"></i>
                                        </a>

                                    <?php endif; ?>
                                    <?php $this->load->view('partials/equipment_modal', ['val' => $val]); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php $this->load->view('partials/equipment_modal', ['val' => FALSE]); ?>
</section>
<script>
    $(document).on('click', '.group-delete', function () {
        var obj = $(this);
        var id = $(this).attr('data-id');
        var status = $(obj).attr('data-enabled');
        var table = $(obj).attr('data-table');
        var disabled = 0;

        if (confirm('Are you sure?')) {
            if ($(this).attr('data-enabled') == 'disabled')
                disabled = 1;
            $.post(baseUrl + 'estimates/delete_vehicle', {id: id, disabled: disabled}, function (resp) {
                if (resp.status == 'ok') {
                    if (status == 'enabled') {
                        $(obj).attr('data-enabled', 'disabled').removeClass('btn-info').addClass('btn-danger');
                        $(obj).find('i').removeClass().addClass('fa fa-ban');
                    } else {
                        $(obj).attr('data-enabled', 'enabled').removeClass('btn-danger').addClass('btn-info');
                        $(obj).find('i').removeClass().addClass('fa fa-check');
                    }
                    $('#' + table + '-' + status).append($(obj).parent().parent());
                } else
                    alert('Something wrong! Please, try again later');
            }, 'json');
            return false;
        }

        return false;
    });
    $(document).ready(function () {
        Common.mask_currency();

        $(".vehicleOptions").select2({
            placeholder: "Type Options",
            tags: [],
            formatNoMatches: function () {
                return '';
            },
            allowClear: true,
            multiple: true,
            separator: "|"
        });
        $.each($('.vehicleOptions'), function (key, elem) {
            $(elem).select2("container").find("ul.select2-choices").sortable({
                containment: 'parent',
                start: function () {
                    $(".vehicleOptions").select2("onSortStart");
                },
                update: function () {
                    $(".vehicleOptions").select2("onSortEnd");
                }
            });
        });
        $('.addEquipment').on('click', function () {
            $('#addEquipment form').find('.vehicleOptions').select2("val", "");
            $('#addEquipment form input').prop('checked', false);
            $('#addEquipment form input').not('[type="radio"]').val('');
            $('#addEquipment form input[name="vehicle_trailer"]:first').prop('checked', true);
        });
    });

    function prevalidation(form) {
        var obj = form;
        var error = false;
        var input = $(obj).find('[name="vehicle_name"]');
        $(input).parent().removeClass('has-error');
        if ($(input).val() == '') {
            error = true;
            $(input).parent().addClass('has-error');
            return false;
        }
        return true;

    }
</script>
<?php $this->load->view('includes/footer'); ?>

    
