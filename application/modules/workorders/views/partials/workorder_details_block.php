<section class="panel panel-default p-n" id="workorder-detaild-block">

    <!-- Workorder Details Header -->
    <header class="panel-heading">
        Workorder Details
        <div class="pull-right">
            <?php if ($payments_data && !empty($payments_data)): ?>
                <label class="m-n text-nowrap"><i class="fa fa-money text-success"></i>&nbsp;Deposit amount: <?php echo money(array_sum(array_column($payments_data, 'payment_amount'))); ?></label>
            <?php else: ?>
                <label class="m-n text-nowrap"><i class="fa fa-money text-success"></i>&nbsp;Deposit amount: <?php echo money(0); ?></label>
            <?php endif; ?>
        </div>
    </header>

    <!-- Data Display -->
    <div class="panel-body p-bottom-0" style="height: 400px">

        <form>
        <div class="row" style="display: flex; flex-wrap: wrap;">
            <div class="col-lg-6 col-sm-12">
                <!-- Extra note for crew-->
                <div style="position: relative">
                    <label class="m-n text-nowrap"><i class="fa fa-list-alt text-primary"></i>&nbsp;<b>Office Notes:</b></label>

                    <textarea name="wo_office_notes" id="wo_office_notes" rows="2" data-id="<?php echo $workorder_data->id; ?>" class="form-control p-right-20 workorder-note-text" placeholder="Scheduling preferences, office notes"><?php echo $workorder_data->wo_office_notes; ?></textarea>
                    <a href="#" data-href="#wo_office_notes" data-id="<?php echo $workorder_data->id; ?>" style="display: none" class="btn btn-sm btn-icon btn-default btn-save-note btn-save-office-note"><i class="fa fa-save text-primary"></i></a>

                </div>
            </div>
            <div class="col-lg-6 col-sm-12">
                <div style="position: relative">
                    <label class="m-n text-nowrap"><i class="fa fa-list-alt text-primary"></i>&nbsp;<b>Crew Notes:</b></label>

                    <textarea name="estimate_crew_notes" id="estimate_crew_notes" rows="2" data-id="<?php echo $workorder_data->id; ?>" class="form-control p-right-20 workorder-note-text" placeholder="Write some notes to the crew"><?php echo $workorder_data->estimate_crew_notes; ?></textarea>
                    <a href="#" data-href="#estimate_crew_notes" data-id="<?php echo $workorder_data->id; ?>" style="display: none" class="btn btn-sm btn-icon btn-default btn-save-note btn-save-crew-note"><i class="fa fa-save text-primary"></i></a>
                </div>
            </div>
            <?php if($workorder_data->wo_confirm_how): ?>
            <div class="col-lg-12 col-sm-12">
                <div class="p-top-15">
                    <label class="m-n"><i class="fa fa-list-alt text-primary"></i>&nbsp;<b>Confirmed How:</b>&nbsp;<?php echo $workorder_data->wo_confirm_how; ?></label>
                </div>
            </div>

            <?php endif; ?>

            <div class="col-lg-12 col-sm-12 m-top-5">

                <section class="panel panel-default workorders-profile-files row no-border p-relative" id="workorders-profile-files" style="margin-bottom: 0">

                </section>

            </div>            

                <?php  $hidden = array(
                    'client_id' => $client_data->client_id,
                    'estimate_id' => $estimate_data->estimate_id,
                    'workorder_id' => $workorder_data->id,
                    'workorder_no' => $workorder_data->workorder_no);
                echo form_hidden($hidden); ?>
                <!-- /Confirmed How -->

            <div class="col-md-12 col-sm-12">
                <h5 class="text-left text-muted">
                    <b class="bg-white"><i class="fa fa-info-circle text-primary"></i>&nbsp;Workorder Requirements</b>
                </h5>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <label><i class="fa fa-users text-warning"></i>&nbsp;<b>Crew:</b></label>
                        <ul class="list-group no-radius">
                        <?php if (!empty($estimate_crews_data)) : ?>
                            <?php foreach ($estimate_crews_data as $key => $estimate_crew) : ?>
                            <li class="list-group-item p-5 p-left-15">
                                <?php echo $estimate_crew['crew_name']; ?>
                                <?php if (isset($estimate_crew['estimate_crew_team']) && $estimate_crew['estimate_crew_team']) : ?>
                                    &nbsp;(Crew members: <?php echo $estimate_crew['estimate_crew_team']; ?>)
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item bg-light text-center text-muted">No data</li>
                        <?php endif; ?>
                        </ul>
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <label><i class="fa fa-truck text-warning"></i>&nbsp;<b>Equipment:</b></label>
                        <ul class="list-group no-radius">
                        <?php if(isset($estimate_equipments) && $estimate_equipments && !$estimate_equipments->isEmpty()
                            && $estimate_equipments->pluck('equipment.vehicle_name')->filter()->count()): ?>
                            <?php foreach ($estimate_equipments as $equipment): ?>
                            <?php if($equipment->equipment || $equipment->attachment): ?>
                                <li class="list-group-item  p-5 p-left-15">
                                    <?php if($equipment->equipment && $equipment->equipment->vehicle_name): ?>
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
                                </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item bg-light text-center text-muted">No data</li>
                        <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
        </form>
    </div>

</section>

<script id="workorders-profile-files-tmp" type="text/x-jsrender">
    <?php echo $this->load->view('workorders/partials/profile_files', ["files"=>$files], true);?>
</script>
<script>

    window.workorder_files = <?php echo json_encode($files); ?>;
    window.estimate_no = '<?php echo $estimate_data->estimate_no; ?>';
    window.workorder_pdf_files = <?php echo json_encode($pdfFiles); ?>;
</script>