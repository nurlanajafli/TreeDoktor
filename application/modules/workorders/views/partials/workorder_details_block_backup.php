<section class="panel panel-default p-n">

    <!-- Workorder Details Header -->
    <header class="panel-heading">Workorder Details</header>

    <!-- Data Display -->
    <div class="panel-body p-bottom-0" style="height: 400px">

        <form>
            <div class="row" style="display: flex; flex-wrap: wrap;">
                <div class="col-md-6 col-sm-12">
                    <!-- Extra note for crew-->
                    <div class="p-top-5" style="position: relative">
                        <label class="m-n text-nowrap"><i class="fa fa-list-alt text-primary"></i>&nbsp;<b>Office Notes:</b></label>

                        <textarea name="wo_office_notes" id="wo_office_notes" rows="2" data-id="<?php echo $workorder_data->id; ?>" class="form-control p-right-20 workorder-note-text" placeholder="Scheduling preferences, office notes"><?php echo $workorder_data->wo_office_notes; ?></textarea>
                        <a href="#" data-href="#wo_office_notes" data-id="<?php echo $workorder_data->id; ?>" style="display: none" class="btn btn-sm btn-icon btn-default btn-save-note btn-save-office-note"><i class="fa fa-save text-primary"></i></a>

                    </div>

                    <div class="p-top-5" style="position: relative">
                        <label class="m-n text-nowrap"><i class="fa fa-list-alt text-primary"></i>&nbsp;<b>Crew Notes:</b></label>

                        <textarea name="estimate_crew_notes" id="estimate_crew_notes" rows="2" data-id="<?php echo $workorder_data->id; ?>" class="form-control p-right-20 workorder-note-text" placeholder="Write some notes to the crew"><?php echo $workorder_data->estimate_crew_notes; ?></textarea>
                        <a href="#" data-href="#estimate_crew_notes" data-id="<?php echo $workorder_data->id; ?>" style="display: none" class="btn btn-sm btn-icon btn-default btn-save-note btn-save-crew-note"><i class="fa fa-save text-primary"></i></a>

                    </div>

                    <div class="p-top-15">
                        <label class="m-n"><i class="fa fa-list-alt text-primary"></i>&nbsp;<b>Confirmed How:</b>&nbsp;<?php echo $workorder_data->wo_confirm_how; ?></label>
                    </div>

                </div>

                <div class="col-lg-6 col-sm-12">
                    <div class="p-top-5">
                        <?php if ($payments_data && !empty($payments_data)): ?>
                            <label class="m-n text-nowrap"><i class="fa fa-money text-success"></i>&nbsp;<b>Deposit amount:</b> <?php echo money(array_sum(array_column($payments_data, 'payment_amount'))); ?></label>
                        <?php else: ?>
                            <label class="m-n text-nowrap"><i class="fa fa-money text-success"></i>&nbsp;<b>Deposit amount:</b> <?php echo money(0); ?></label>
                        <?php endif; ?>
                    </div>
                    <div class="scrollable" style="max-height:135px;">

                        <section class="panel panel-default m-bottom-10" style="box-shadow: none;border-right: 0; border-left: 0">
                            <ul class="list-group alt" id="payment-files-list">
                                <?php if (isset($files) && !empty($files)) : ?>
                                    <?php foreach ($files as $file) : ?>
                                        <?php $filepath = 'uploads/payment_files/' . $workorder_data->client_id . '/' . $estimate_data->estimate_no . '/' . $file; ?>
                                        <li class="list-group-item">
                                            <div class="media">
                                                <span class="pull-left thumb-sm p-top-10" style="width: 20px"><i class="fa fa-paperclip h4 text-danger"></i></span>
                                                <div class="pull-right text-success m-t-xs">
                                                    <a href="#" class="btn btn-rounded btn-sm btn-icon btn-danger deleteEstimatePhoto" role="button" data-estimate_id="<?php echo $workorder_data->estimate_id; ?>" data-path="<?php echo $filepath; ?>">
                                                        <i class="fa fa-trash-o"></i>
                                                    </a>
                                                </div>
                                                <div class="media-body">
                                                    <div>
                                                        <a target="_blank" class="file-list-name-block" href="<?php echo base_url($filepath); ?>">
                                                            <?php echo $file; ?>
                                                        </a>
                                                    </div>

                                                    <label class="checkbox m-t-none m-b-none">
                                                        <input type="checkbox" <?php if(array_search($filepath, $pdfFiles) !== FALSE) : ?>checked="checked" <?php endif; ?>data-file-name="<?php echo $filepath; ?>">
                                                        Print in PDF
                                                    </label>

                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="list-group-item">
                                        <div class="media">
                                            <div class="media-body">
                                                <div class="h5 text-muted text-center">
                                                    No Files
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </section>

                    </div>
                    <div class="text-center">
                        <span class="btn btn-primary btn-file btn-reverse btn-rounded m-top-5 bg-white">Choose File&nbsp;<i class="fa fa-cloud-upload"></i>
                            <input type="file" name="file" id="fileToUpload" class="btn-upload">
                        </span>
                        <img id="preloader" src="/assets/img/ajax-loader.gif" style="display:none;">
                    </div>
                </div>

                <?php  $hidden = array(
                    'client_id' => $client_data->client_id,
                    'estimate_id' => $estimate_data->estimate_id,
                    'workorder_id' => $workorder_data->id,
                    'workorder_no' => $workorder_data->workorder_no);
                echo form_hidden($hidden); ?>
                <!-- /Confirmed How -->

                <div class="col-md-12 col-sm-12">
                    <hr class="m-top-15 m-bottom-10">
                    <h5 class="text-center text-muted" style="margin-top: -20px">
                        <b class="bg-white p-5"><i class="fa fa-info-circle text-primary"></i>&nbsp;Workorder Requirements</b>
                    </h5>
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <label><i class="fa fa-users text-warning"></i>&nbsp;<b>Crew:</b></label>
                            <?php if (!empty($estimate_crews_data)) : ?>
                                <section class="comment-list block">
                                    <?php foreach ($estimate_crews_data as $key => $estimate_crew) : ?>
                                        <article id="comment-id-<?php echo $key; ?>" class="comment-item">
                                            <a class="pull-left thumb-sm avatar"></a>
                                            <span class="arrow left"></span>
                                            <section class="comment-body panel panel-default m-bottom-5">
                                                <div class="panel-body">
                                                    <?php echo $estimate_crew['crew_name']; ?>
                                                    <?php if (isset($estimate_crew['estimate_crew_team']) && $estimate_crew['estimate_crew_team']) : ?>
                                                        &nbsp;(Crew members: <?php echo $estimate_crew['estimate_crew_team']; ?>)
                                                    <?php endif; ?>
                                                </div>
                                            </section>
                                        </article>

                                    <?php endforeach; ?>
                                </section>
                            <?php endif; ?>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <label><i class="fa fa-truck text-warning"></i>&nbsp;<b>Equipment:</b></label>
                            <?php if(isset($estimate_equipments) && $estimate_equipments): ?>
                                <section class="comment-list block">
                                    <?php foreach ($estimate_equipments as $equipment): ?>
                                        <article id="equipment-id-<?php echo $key; ?>" class="comment-item">
                                            <a class="pull-left thumb-sm avatar"></a>
                                            <span class="arrow left"></span>
                                            <section class="comment-body panel panel-default m-bottom-5">
                                                <div class="panel-body">
                                                    <?php if($equipment->equipment->vehicle_name): ?>
                                                        <?php echo $equipment->equipment->vehicle_name; ?>
                                                        <?php if($equipment->equipment_item_option && count(json_decode($equipment->equipment_item_option, true))): ?>
                                                            <?php $options = json_decode($equipment->equipment_item_option, true);?>
                                                            (<?php echo implode(' or ', $options); ?>)
                                                        <?php else: ?>
                                                            (Any)
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <?php if($equipment->attachment && $equipment->attachment->vehicle_name): ?>
                                                        <?php if(!$equipment->equipment || !$equipment->equipment->vehicle_name): ?>
                                                            <i class="fa fa-check"></i>
                                                            <?php echo $equipment->attachment->vehicle_name; ?>
                                                        <?php else: ?>
                                                            , <?php echo $equipment->attachment->vehicle_name; ?>
                                                        <?php endif; ?>
                                                        <?php if($equipment->equipment_attach_option && count(json_decode($equipment->equipment_attach_option, true))): ?>
                                                            <?php $att_options = json_decode($equipment->equipment_attach_option, true); ?>
                                                            (<?php echo implode(' or ', $att_options); ?>)
                                                        <?php else: ?>
                                                            (Any)
                                                        <?php endif; ?>

                                                    <?php endif; ?>
                                                </div>
                                            </section>
                                        </article>
                                    <?php endforeach; ?>
                                </section>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

</section>

<style type="text/css">
    .btn-primary.btn-reverse{
        color: #65bd77!important;
    }

    .btn-primary.btn-reverse:hover{
        color: #fff!important;
    }
    .text-nowrap{ white-space: nowrap; overflow: hidden;}
</style>