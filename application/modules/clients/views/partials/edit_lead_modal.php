<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/leads/edit_lead_modal.css?v=1.00'); ?>" type="text/css" />

<div class="leadForm-<?php echo $row['lead_id']?> clear edit-lead-form">
    <?php echo form_hidden(['client_id' => $row['client_id'], 'lead_id' => $row['lead_id'], 'lead_no' => $row['lead_no']]); ?>

    <div class="col-md-12">
        <div class="line line-dashed line-lg pull-in m-top-5"></div>
        <div class="row">
            <div class="col-md-2">
                <span class="fa-stack fa-1x pull-left m-r-sm">
                    <i class="fa fa-circle fa-stack-2x text-primary"></i>
                    <i class="fa fa fa-cog fa-stack-1x text-white"></i>
                </span>
                <strong style="line-height: 26px;">Options</strong>
                <br class="visible-sm visible-xs">
                <br class="visible-sm visible-xs">
            </div>
            <div class="col-md-10">
                <div class="row">
                    <div class="col-md-6 text-right">
                        <?php if(!$row['lead_status_estimated']) :   ?>
                        <div class="form-group m-bottom-5 row">
                            <label class="control-label col-md-4 hidden-sm hidden-xs">Lead Status:</label>
                            <label class="control-label col-md-4 col-sm-3 text-left visible-sm visible-xs">Lead Status:</label>
                            <div class="col-md-8 col-sm-9">
                                <select name="set_lead_status" class="form-control" onchange="Leads.reasonFunction('<?php echo $row['lead_id']; ?>');">
                                    <?php foreach ($lead_statuses as $k=>$v) : ?>
                                        <option data-status-info="<?php  echo htmlentities(json_encode($v->mdl_leads_reason));?>"
                                            value="<?php echo $v->lead_status_id; ?>"<?php if ($row['lead_status_id'] == $v->lead_status_id) : $lead_reasons = $v->mdl_leads_reason; ?> selected<?php endif; ?>><?php echo $v->lead_status_name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row m-bottom-5 reason-block inline-block" <?php if(!$row['lead_reason_status_id']) : ?>style="display:none"<?php endif;?>>
                            <div class="visible-sm p-2"></div>
                            <label class="control-label col-md-4 hidden-sm hidden-xs">Lead Reason:</label>
                            <label class="control-label col-md-4 col-sm-3 text-left visible-sm visible-xs">Lead Reason:</label>
                            <div class="col-md-8 col-sm-9">
                                <?php  $reason = $row['lead_reason_status_id']; ?>
                                <select name="set_lead_reason_status" class="form-control reason-no-go" <?php if(!$row['lead_reason_status_id']) : ?>disabled="disabled"<?php endif;?>>
                                    <?php if(isset($lead_reasons) && !empty($lead_reasons)) : ?>
                                    <?php foreach ($lead_reasons as $k=>$v) : ?>
                                        <option  
                                            value="<?php echo $v->reason_id; ?>"<?php if ($reason == $v->reason_id) :  ?> selected<?php endif; ?>><?php echo $v->reason_name; ?></option>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>     
                        <?php else: ?>
                            <div class="form-group m-bottom-5 row">
                                <div class="visible-sm p-2"></div>
                                <label class="control-label col-md-4 hidden-sm hidden-xs">Lead Status:</label>
                                <label class="control-label col-md-4 col-sm-3 text-left visible-sm visible-xs">Lead Status:</label>
                                <div class="col-md-8 col-sm-9 text-left">
                                    <section class="panel panel-default m-n">
                                        <div class="row m-l-none m-r-none bg-light lter">
                                                <div class="col-sm-12 col-md-12 padder-v b-r b-light  p-top-5 p-bottom-5">
                                                    <span class="fa-stack fa-1x pull-left m-r-lg">
                                                    <i class="fa fa-circle fa-stack-2x text-warning"></i>
                                                    <i class="fa fa-check fa-stack-1x text-white"></i>
                                                </span>
                                                <a class="pull-left" href="#">
                                                    <span class="h4 block m-t-xs"><strong>Estimated</strong></span>
                                                </a>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="form-group m-bottom-5 row">
                            <div class="visible-sm p-2"></div>
                            <label class="control-label col-md-4 hidden-sm hidden-xs">Priority status:</label>
                            <label class="control-label col-md-4 col-sm-3 text-left visible-sm visible-xs">Priority status:</label>
                            <div class="col-md-8 col-sm-9">
                                <?php
                                    $options = ['Regular' => 'Regular', 'Priority' => 'Priority', 'Emergency' => 'Emergency'];
                                    $status = $row['lead_priority'];
                                    echo form_dropdown('set_lead_priority', $options, $status, 'class="form-control"'); 
                                ?>
                            </div>
                        </div>
                        
                        <div class="form-group m-bottom-5 row">
                            <div class="visible-sm p-2"></div>
                            <label class="control-label col-md-4 hidden-sm hidden-xs">Referenced by:</label>
                            <label class="control-label col-md-4 col-sm-3 text-left visible-sm visible-xs">Referenced by:</label>
                            <div class="col-md-8 col-sm-9">
                                <?php
                                    $references = (is_string($references))?json_decode($references):$references;
                                    $lead_reffered_by = ($row['lead_reffered_by'] && !isset($references[$row['lead_reffered_by']]))?'other':$row['lead_reffered_by'];

                                    echo form_dropdown('lead_reff', $references, $lead_reffered_by, 'class="form-control" id="reffered"');
                                ?>
                                <?php if($row['lead_reffered_client']) : ?>
                                <input id="reff_id" name="reff_id" style="width:100%; display:none" data-id="<?php echo $row['lead_reffered_client']; ?>" data-text="<?php echo $row['reffered_client_text']; ?>">
                                <?php elseif($row['lead_reffered_user']): ?>
                                <input id="reff_id" name="reff_id" style="width:100%; display:none"  data-value="<?php echo $row['reffered_user_text']; ?>" data-id="<?php echo $row['lead_reffered_user']; ?>">
                                <?php else: ?>
                                <input id="reff_id" name="reff_id" style="width:100%; display:none">
                                <?php endif; ?>
                                <input name="other_comment" class="form-control other_comment" style="display: none;width:100%;" value="<?php echo $row['lead_reffered_by']; ?>">

                            </div>
                        </div>

                        <div class="form-group m-bottom-5 row">
                            <div class="visible-sm p-2"></div>
                            <label class="control-label col-md-4 show-address-block hidden-sm hidden-xs" style="line-height: 24px" href="#new_add_tab-<?php echo $row['lead_id']; ?>" data-toggle="class:hide" onclick="$(this).parent().find('.another-address-checkbox').trigger('change');">
                                <i class="fa fa-pencil text-danger"></i>&nbsp;Address:
                            </label>
                            <label class="control-label col-md-4 show-address-block col-sm-3 text-left visible-sm visible-xs" style="line-height: 16px" href="#new_add_tab-<?php echo $row['lead_id']; ?>" data-toggle="class:hide" onclick="$(this).parent().find('.another-address-checkbox').trigger('change');">
                                <i class="fa fa-pencil text-danger"></i>&nbsp;Address:
                            </label>

                            <input type="checkbox" name="new_add" class="hide another-address-checkbox" id="new_add-<?php echo $row['lead_id']; ?>" <?php if(element('lead_address', $row, '') && element('lead_address', $row, '')!=element('client_address', $row, '')) : ?>checked="checked"<?php endif; ?>  value="1" data-id="#new_add_tab-<?php echo $row['lead_id']; ?>">

                            <div class="col-md-8 col-sm-9 text-left">
                                <a class="client-tdnh" href="#new_add_tab-<?php echo $row['lead_id']; ?>" data-toggle="class:hide" onclick="$(this).parent().prev().trigger('click');"><?php echo element('lead_address', $row, ''); ?>, <?php echo element('lead_city', $row, ''); ?>, <?php echo element('lead_state', $row, ''); ?>, <?php echo element('lead_zip', $row, ''); ?>, <?php if(isset($row['lead_add_info']) && $row['lead_add_info'] != '') : echo $row['lead_add_info']; endif; ?></a>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-6 text-right">
                        
                        <div class="form-group m-bottom-5 row">
                            <div class="visible-sm p-2"></div>
                            <label class="control-label col-md-4 hidden-sm hidden-xs">Postpone Date:</label>
                            <label class="control-label col-md-4 col-sm-3 text-left visible-sm visible-xs">Postpone Date:</label>
                            <div class="col-md-8 col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input readonly name="postpone_date" class="datepicker form-control to " style="max-width: auto!important" type="text"
                                           value="<?php if ($row['lead_postpone_date'] && $row['lead_postpone_date'] != '0000-00-00') : echo getDateTimeWithDate($row['lead_postpone_date'], 'Y-m-d'); endif; ?>" >
                                </div>
                            </div>
                        </div>

                        <div class="form-group m-bottom-5 row">
                            <div class="visible-sm p-2"></div>
                            <label class="control-label col-md-4 hidden-sm hidden-xs" style="line-height: 24px">Deadlines:</label>
                            <label class="control-label col-md-4 col-sm-3 text-left visible-sm visible-xs">Deadlines:</label>
                            <div class="col-md-8 col-sm-9">
                                <div class="input-group">
                                    <span style="padding: 9px 16px;" class="input-group-addon show-date-original-title" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="How soon are you looking for to get it done?"><i class="fa fa-info"></i></span>
                                    <?php    
                                        $options = [
                                            'Right Away' => 'Right Away',
                                            'Within a month' => 'Within a month',
                                            'Not in a Rush' => 'Not in a Rush'
                                        ];
                                        $status = $row['timing'];
                                        echo form_dropdown('set_lead_timing', $options, $status, 'class="form-control"'); 
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group m-bottom-5 row">
                            <div class="visible-sm p-2"></div>
                            <div class="col-md-4 col-sm-3"></div>
                            <div class="col-md-8 col-sm-9">
                                
                                <a class="btn btn-default btn-block <?php if(element('lead_call', $row, 0)) : ?>active<?php endif; ?>" style="padding: 3px 10px 4px;" href="#lead_call" data-toggle="button" onclick="$($(this).attr('href')).trigger('click');">
                                
                                    <span class="text">
                                        <span class="fa-stack fa-1x pull-left m-r-sm">
                                            <i class="fa fa-circle fa-stack-2x text-danger"></i>
                                            <i class="fa fa-phone fa-stack-1x text-white"></i>
                                        </span><span class="inline-block" style="line-height: 26px;">Call The Client</span> <i class="fa fa-times text-danger"></i>
                                    </span>
                                    <span class="text-active">
                                        <span class="fa-stack fa-1x pull-left m-r-sm">
                                            <i class="fa fa-circle fa-stack-2x text-success"></i>
                                            <i class="fa fa-phone fa-stack-1x text-white"></i>
                                        </span><span class="inline-block" style="line-height: 26px;">Call The Client</span> <i class="fa fa-check text-success"></i>
                                    </span>
                                </a>
                                <input type="checkbox" class="callLead hide" id="lead_call" name="lead_call" <?php if(element('lead_call', $row, 0)) : ?>checked="checked"<?php endif; ?>>
                                

                                <?php /*
                                <div class="checkbox pull-left text-left" style="padding-left: 13px;">
                                    <label class="checkbox-custom h5">
                                        <input type="checkbox" class="callLead hide" id="lead_call" name="lead_call" <?php if(element('lead_call', $row, 0)) : ?>checked="checked"<?php endif; ?>>
                                        <i class="fa fa-fw fa-square-o checked" style="margin-right: 20px;"></i>
                                        Call The Client
                                    </label>
                                </div>
                                <span class="fa-stack fa-1x pull-left m-r-sm" style="margin-top: 5px;margin-left: 14px;">
                                    <i class="fa fa-circle fa-stack-2x text-success"></i>
                                    <i class="fa fa-phone fa-stack-1x text-white"></i>
                                </span>
                                */ ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 <?php if(!element('lead_address', $row, '') || element('lead_address', $row, '')==element('client_address', $row, '')) : ?>hide<?php endif ;?>" id="new_add_tab-<?php echo $row['lead_id']; ?>">
        <div class="line line-dashed line-lg pull-in m-top-5"></div>
        
        <div class="row">
            <div class="col-md-2">
                <span class="fa-stack fa-1x pull-left m-r-sm">
                    <i class="fa fa-circle fa-stack-2x text-success"></i>
                    <i class="fa fa-map-marker fa-stack-1x text-white"></i>
                </span>
                <strong style="line-height: 26px;">Address</strong>
                <br class="visible-sm visible-xs">
                <br class="visible-sm visible-xs">
            </div>

            <div class="col-md-10">   
                <div class="row">
                    <div class="col-md-6 text-right">
                        <div class="row form-group m-bottom-5">
                            <div class="visible-sm p-2"></div>
                            <label class="control-label col-md-4 hidden-sm hidden-xs" style="line-height: 24px">Address:</label>
                            <label class="control-label col-md-4 col-sm-3 text-left visible-sm visible-xs">Address:</label>

                            <div class="col-md-8 col-sm-9">
                                <input type="text" class="form-control" name="new_address" value="<?php if (isset($row['lead_address'])) : echo $row['lead_address']; endif; ?>" data-autocompleate="true" data-part-address="address">
                            </div>
                        </div>

                        <div class="form-group row m-bottom-5">
                            <div class="visible-sm p-2"></div>
                            <label class="control-label col-md-4 hidden-sm hidden-xs">City:</label>
                            <label class="control-label col-md-4 col-sm-3 text-left visible-sm visible-xs">City:</label>

                            <div class="col-md-8 col-sm-9">
                                <input type="text" data-part-address="locality" class="form-control" name="new_city" value="<?php if (isset($row['lead_city'])) : echo $row['lead_city']; endif; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 text-right">
                        <div class="row form-group m-bottom-5">
                            <div class="visible-sm p-2"></div>
                            <label class="control-label col-md-4 hidden-sm hidden-xs">State/Zip Code:</label>
                            <label class="control-label col-md-4 col-sm-3 text-left visible-sm visible-xs">State/Zip Code:</label>

                            <div class="col-md-8 col-sm-9">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <input type="text" name="new_state" class="form-control pull-left" value="<?php if (isset($row['lead_state'])) : echo $row['lead_state']; endif; ?>" data-part-address="administrative_area_level_1">
                                    </div>
                                    <div class="col-xs-6">
                                        <input type="text" name="new_zip" class="form-control" value="<?php if (isset($row['lead_zip'])) : echo $row['lead_zip']; endif; ?>" data-part-address="postal_code">
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" class="new_lat" data-part-address="lat" name="new_lat" value="<?php if (isset($row['latitude'])) : echo $row['latitude']; endif; ?>">
                            <input type="hidden" class="new_lon" data-part-address="lon" name="new_lon" value="<?php if (isset($row['longitude'])) : echo $row['longitude']; endif; ?>">
                        </div>

                        <div class="row form-group m-bottom-5">
                            <div class="visible-sm p-2"></div>
                            <label class="control-label col-md-4 hidden-sm hidden-xs">Add. Info:</label>
                            <label class="control-label col-md-4 col-sm-3 text-left visible-sm visible-xs">Add. Info:</label>
                            <div class="col-md-8 col-sm-9">
                                <?php if (isset($row['lead_add_info']) && $row['lead_add_info']) : ?>
                                    <input type="text" name="lead_add_info" class="form-control" value="<?php echo $row['lead_add_info']; ?>" >
                                <?php elseif (isset($row['client_main_intersection'])) : ?>
                                    <input type="text" name="lead_add_info" class="form-control" value="<?php echo $row['client_main_intersection']; ?>" >
                                <?php else : ?>
                                    <input type="text" name="lead_add_info" class="form-control" value="" >
                                <?php endif;?>
                            </div>
                            <!--<label class="control-label col-md-4 hidden-sm hidden-xs">Country:</label>
                            <label class="control-label col-md-4 col-sm-3 text-left visible-sm visible-xs">Country:</label>

                            <div class="col-md-8 col-sm-9">
                                <input type="text" name="lead_country" class="form-control" value="<?php /*if (isset($row['lead_country'])) : echo $row['lead_country']; endif; */?>" data-part-address="country">
                            </div>-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        
    <div class="col-md-12">
        <div class="line line-dashed line-lg pull-in m-top-5"></div>
        <div class="row">
            <div class="col-md-2">
                <span class="fa-stack fa-1x pull-left m-r-sm">
                    <i class="fa fa-circle fa-stack-2x text-danger"></i>
                    <i class="fa fa fa-users fa-stack-1x text-white"></i>
                </span>
                <strong style="line-height: 26px;">Estimator</strong>
                <br class="visible-sm visible-xs">
                <br class="visible-sm visible-xs">
            </div>
            <div class="col-md-10">
                <div class="row">
                    <div class="col-md-6 text-right">
                        <div class="form-group row" style="margin-bottom: 9px">
                            
                            <div class="visible-sm p-2"></div>
                            <label class="control-label col-md-4 hidden-sm hidden-xs">Assigned To:</label>
                            <label class="control-label col-md-4 col-sm-3 text-left visible-sm visible-xs">Assigned To:</label>

                            <div class="col-md-8 col-sm-9">
                                <select name="assigned_to" class="form-control">
                                    <option value="none">Not assigned</option>
                                    <?php if(isset($estimators) && !empty($estimators)): ?>

                                        <?php foreach ($estimators as $estimator) : ?>
                                            <option value="<?php echo $estimator->id; ?>"<?php if($row['lead_estimator'] == $estimator->id) : ?>selected="selected"<?php endif; ?> >
                                            <?php echo $estimator->firstname . "&nbsp;" . $estimator->lastname; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                    </div>
                    <?php $lead_appointments = lead_appointments(isset($schedule_appointments)?$schedule_appointments:[], $row['lead_id']); ?>
                    <div class="col-md-6">
                        <div class="form-group row m-bottom-5">
                            
                            <div class="visible-sm p-2"></div>
                            <label class="control-label col-md-4 p-top-10 text-right hidden-sm hidden-xs">
                                <?php if(!empty($lead_appointments)): ?>
                                    <a href="#" id="init_appointment_modal" class="client-tdnh"><i class="fa fa-pencil"></i>&nbsp;Meetings:</a>
                                <?php endif; ?>
                            </label>
                            <label class="control-label col-md-4 col-sm-3 text-left visible-sm visible-xs">
                                <?php if(!empty($lead_appointments)): ?>
                                    <a href="#" id="init_appointment_modal" class="client-tdnh"><i class="fa fa-pencil"></i>&nbsp;Meetings:</a>
                                <?php endif; ?>
                            </label>


                            <div class="col-md-8 col-sm-9">
                                <?php $this->load->view('clients/appointment/new_appointment_block'); ?>
                                <?php if(!empty($lead_appointments)): ?>
                                    <?php foreach($lead_appointments as $appointment): ?>
                                        <section class="panel clearfix lter panel-warning m-bottom-5">
                                            <div class="panel-body" style="padding: 0px 2px">
                                                
                                                <span class="fa-stack pull-left <?php /*m-l-xs m-r*/ ?>">
                                                    <i class="fa fa-circle text-danger fa-stack-2x"></i>
                                                    <strong class="text-white fa-stack-1x"><?php if(strlen($appointment['emailid']) == 2) { echo $appointment['emailid']; } else {
                                                        echo strtoupper(substr($appointment['ass_firstname'], 0, 1)) . strtoupper(substr($appointment['ass_lastname'], 0, 1));
                                                        } ?></strong>
                                                </span>
                                                
                                                <div class="pull-left text-left p-left-5">
                                                    <a href="#" class="text-info"><?php echo (isset($appointment['ass_firstname']))?$appointment['ass_firstname'].' '.$appointment['ass_lastname']: ''; ?> <i class="fa fa-user"></i></a>

                                                    <small class="block text-muted"><i class="fa fa-calendar text-danger"></i>&nbsp;
                                                        <?php echo (isset($appointment['task_date']))?(new \DateTime($appointment['task_date']))->format(getDateFormat()): ''; ?>
                                                        &nbsp;<i class="fa fa-clock-o text-danger"></i>
                                                        &nbsp;<?php echo (isset($appointment['task_start']))?date(getPHPTimeFormatWithOutSeconds(), strtotime($appointment['task_start'])): ''; ?> /
                                                        <?php echo (isset($appointment['task_end']))?date(getPHPTimeFormatWithOutSeconds(), strtotime($appointment['task_end'])): ''; ?></small>
                                                </div>
                                                
                                            </div>
                                        </section>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <input type="checkbox" class="scheduledLead hidden" name="lead_scheduled" <?php if(isset($row['lead_scheduled']) && $row['lead_scheduled']) : ?>checked="checked"<?php endif; ?>>

                                <div class="clearfix"></div>
                                <?php if(empty($lead_appointments)): ?>
                                <a href="#" id="init_appointment_modal" class="text-danger h5 m-top-10 block client-tdnh"><i class="fa fa-plus"></i>&nbsp;Schedule Appointment&nbsp;<i class="fa fa-map-marker"></i></a>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
                        

   

    <div class="col-md-12">
        <div class="line line-dashed line-lg pull-in"></div>
        
        <div class="row">
            
            <div class="lead-description-container">
                <strong class="control-label" style="line-height: 26px;">
                    <span class="fa-stack fa-1x pull-left m-r-sm">
                        <i class="fa fa-circle fa-stack-2x icon-muted text-warning"></i>
                        <i class="fa fa-pencil fa-stack-1x text-white"></i>
                    </span>
                    Description
                </strong>

                <div class="form-group">
                    <div>
                        <?php  $options = array(
                        'name' => 'set_lead_discription',
                        'rows' => '8',
                        'class' => 'input-block-level form-control client-textarea-description',
                        'value' => $row['lead_body']);

                        echo form_textarea($options); ?>
                    </div>
                </div>
            </div>

            <div class="lead-tags-parent-container" >
                <div class="lead-tags-container">
                    <strong class="control-label" style="line-height: 26px;">
                    <span class="fa-stack fa-1x pull-left m-r-sm">
                        <i class="fa fa-circle fa-stack-2x icon-muted text-warning"></i>
                        <i class="fa fa-wrench fa-stack-1x text-white"></i>
                    </span>
                        Services
                    </strong>
                    <div class="form-group">
                        <input type="hidden"  name="est_services" autocomplete="false" class="est_services w-100"
                               value="<?php echo !empty($est_services) ? $est_services : ''; ?>"
                               data-value="<?php echo !empty($est_services) ? $est_services : ''; ?>"
                               data-toggle="tooltip" data-placement="top" title="" data-original-title=""/>
                    </div>
                </div>

                <div class="lead-tags-container">
                    <strong class="control-label" style="line-height: 26px;">
                    <span class="fa-stack fa-1x pull-left m-r-sm">
                        <i class="fa fa-circle fa-stack-2x icon-muted text-warning"></i>
                        <i class="fa fa-wrench fa-stack-1x text-white"></i>
                    </span>
                        Products
                    </strong>
                    <div class="form-group">
                        <input type="hidden"  name="est_products" autocomplete="false" class="est_products w-100"
                               value="<?php echo !empty($est_products) ? $est_products : ''; ?>"
                               data-value="<?php echo !empty($est_products) ? $est_products : ''; ?>"
                               data-toggle="tooltip" data-placement="top" title="" data-original-title=""/>
                    </div>
                </div>

                <div class="lead-tags-container">
                    <strong class="control-label" style="line-height: 26px;">
                    <span class="fa-stack fa-1x pull-left m-r-sm">
                        <i class="fa fa-circle fa-stack-2x icon-muted text-warning"></i>
                        <i class="fa fa-wrench fa-stack-1x text-white"></i>
                    </span>
                        Bundles
                    </strong>
                    <div class="form-group">
                        <input type="hidden"  data-1="2" name="est_bundles" autocomplete="false" class="est_bundles w-100"
                               value="<?php echo !empty($est_bundles) ? $est_bundles : ''; ?>"
                               data-value="<?php echo !empty($est_bundles) ? $est_bundles : ''; ?>"
                               data-toggle="tooltip" data-placement="top" title="" data-original-title=""/>
                    </div>
                </div>
            </div>
        </div>
        <div class="lead-files">
            <label class="m-t-sm"><strong>Files:</strong></label><br>            
        <?php $files = bucketScanDir('uploads/clients_files/' . $row['client_id'] . '/leads/' . str_pad($row['lead_id'], 5, '0', STR_PAD_LEFT) . '-L/'); ?>
        <?php if ($files && count($files)) : ?>
                <?php foreach ($files as $img) : ?>
                <div class="client-files-block">
                <?php $img_url = 'uploads/clients_files/' . $row['client_id'] . '/leads/' . str_pad($row['lead_id'], 5, '0', STR_PAD_LEFT) . '-L/' . $img; ?>
                    <?php $img_parts = explode('.',$img); if(end($img_parts) != 'pdf') { ?>
                    <a href="<?= base_url($img_url);?>" data-lightbox="leadfile-<?= $row['lead_id'] ?>" data-lead_file="<?= $img ?>"><img src="<?= base_url($img_url); ?>" ></a>
                    <?php } else { ?>
                    <a href="<?= base_url($img_url);?>" target="_blank" data-lead_file="<?= $img ?>"><?= $img ?></a>
                    <?php } ?>
                    <a class="domfile" href="#" data-lead_file="<?= $img ?>" onclick="remove_lead_file(<?=$row['lead_id']?>, <?="'".$img."'"?>, <?=$row['client_id']?>, 1)">Remove file</a>
                </div>
                <?php endforeach; ?>
        <?php endif; ?>
        </div>
        <div class="dropzone dropzone-lead client-textarea-description"></div>
    </div>
    <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
</div>

<script>
    const refferedOption = {};
    <?php if($row['lead_reffered_client']): ?>
        refferedOption.id = <?php echo $row['lead_reffered_client']; ?>;
        refferedOption.text = '<?php echo $row['reffered_client_text']; ?>';
    <?php elseif($row['lead_reffered_user']): ?>
        refferedOption.id = <?php echo $row['lead_reffered_user']; ?>;
        refferedOption.text = '<?php echo $row['reffered_user_text']; ?>';
    <?php endif; ?>
</script>
<!-- /Lead display -->

<?php //$this->load->view('leads/lead_services_list'); ?>
