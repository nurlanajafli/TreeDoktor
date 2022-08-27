<!-- Client Files Header-->
<header class="panel-heading h4"><i class="fa fa-plus-square">&nbsp;</i>
    Create new lead for&nbsp;<span class="text-warning"><?php echo $client_data->client_name; ?></span>
</header>
<div class="p-10">
    <?php  $options = array(
        'name' => 'new_client_lead',
        'rows' => '5',
        'class' => 'form-control',
        'placeholder' => 'New client lead. Get as much info as possible...',
        'value' => set_value('new_client_lead'));?>
    <?php $hidden = array('client_id' => $client_data->client_id); ?>
    <?php echo form_hidden($hidden); ?>
    <?php echo form_textarea($options) ?>
</div>
<div class="p-10">
    <div class="control-group row">
        <div class="col-xs-12">
            <label class="control-label"><strong>Services:</strong></label>

            <div class="controls pos-rlt">
              <input type="hidden" name="est_services" autocomplete="false" class="est_services w-100" value=""
              data-value="" data-toggle="tooltip" data-placement="top" title="" data-original-title=""/>
            </div>
            <?php if(!empty($products)): ?>
                <label class="control-label"><strong>Products:</strong></label>
                <div class="controls pos-rlt">
                    <input type="hidden" name="est_products" autocomplete="false" class="est_products w-100" value=""
                           data-value="" data-toggle="tooltip" data-placement="top" title="" data-original-title=""/>
                </div>
            <?php endif; if (!empty($bundles)): ?>
                <label class="control-label"><strong>Bundles:</strong></label>
                <div class="controls pos-rlt">
                    <input type="hidden" name="est_bundles" autocomplete="false" class="est_bundles w-100" value=""
                           data-value="" data-toggle="tooltip" data-placement="top" title="" data-original-title=""/>
                </div>
            <?php endif; ?>
            <?php if (!empty($estimatorsList) && !empty(json_decode($estimatorsList))): ?>
                <label class="control-label"><strong>Estimators</strong></label>
                <div class="controls pos-rlt m-b-xs">
                    <input type="hidden" name="estimators" autocomplete="false" class="estimators w-100" value="" style="overflow-y: auto"
                           data-value=""  data-toggle="tooltip" data-placement="top" title="" data-original-title=""/>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="p-10">
    <div class="row">
        <div class="col-lg-6 col-md-6">
            <input placeholder="Postpone Date" name="postpone_date" class="datepicker form-control to" type="text" value="<?php echo date(getDateFormat()); ?>"/>
        </div>
        <div class="col-lg-6 col-md-6">
            <input type="button" class="btn btn-success scheduledLead pull-right" value="Schedule Appointment">
        </div>
    </div>
</div>

<div class="p-10">
    <div class="control-group row">
        <div class="col-xs-12">
            <label class="control-label"><strong>Priority status:</strong></label>
            <div class="btn-group btn-group-justified" data-toggle="buttons">
                <label class="btn btn-sm btn-info <?php if(isset($new_lead_priority) && $new_lead_priority == 'Regular'): ?>active<?php endif; ?><?php if(!isset($new_lead_priority) || !$new_lead_priority): ?>active<?php endif; ?>">
                    <input type="radio" name="new_lead_priority" <?php if(isset($new_lead_priority) && $new_lead_priority == 'Regular'): ?>checked="checked"<?php endif; ?><?php if(!isset($new_lead_priority) || !$new_lead_priority): ?>checked="checked"<?php endif; ?> value="Regular"><i class="fa fa-check text-active"></i> Regular
                </label>
                <label class="btn btn-sm btn-success <?php if(isset($new_lead_priority) && $new_lead_priority == 'Priority'): ?> active<?php endif; ?>">
                    <input type="radio" name="new_lead_priority" <?php if(isset($new_lead_priority) && $new_lead_priority == 'Priority'): ?>checked="checked"<?php endif; ?>  value="Priority"><i class="fa fa-check text-active"></i> Priority
                </label>
                <label class="btn btn-sm btn-primary <?php if(isset($new_lead_priority) && $new_lead_priority == 'Emergency'): ?> active<?php endif; ?>">
                    <input type="radio" name="new_lead_priority" <?php if(isset($new_lead_priority) && $new_lead_priority == 'Emergency'): ?>checked="checked"<?php endif; ?>  value="Emergency"><i class="fa fa-check text-active"></i> Emergency
                </label>
            </div>
        </div>
    </div>
</div>
<div class="p-10">
    <div class="control-group row">
        <div class="col-xs-12">
            <label class="text-success"><strong>Estimate:</strong></label>
            <div class="btn-group btn-group-justified" data-toggle="buttons">
                <label class="btn btn-sm btn-info">
                    <input type="radio" name="preliminary_estimate" id="preliminary-estimate-small" value="small"><i class="fa fa-check text-active"></i> Small
                </label>
                <label class="btn btn-sm btn-success">
                    <input type="radio" name="preliminary_estimate" id="preliminary-estimate-medium" value="medium"><i class="fa fa-check text-active"></i> Medium
                </label>
                <label class="btn btn-sm btn-primary">
                    <input type="radio" name="preliminary_estimate" id="preliminary-estimate-big" value="big"><i class="fa fa-check text-active"></i> Big
                </label>
            </div>
        </div>
    </div>
</div>
<div class="p-10">
    <div class="control-group row">
        <div class="col-xs-12">
            <label class="control-label"><strong>How soon are you looking for to get it done?</strong></label>
            <div class="btn-group btn-group-justified" data-toggle="buttons">
                <label class="btn btn-sm btn-info <?php if(isset($new_lead_timing) && $new_lead_timing == 'Right Away'): ?> active<?php endif; ?>">
                    <input type="radio" name="new_lead_timing" <?php if(isset($new_lead_timing) && $new_lead_timing == 'Right Away'): ?>checked="checked"<?php endif; ?> value="Right Away"><i class="fa fa-check text-active"></i> Right Away
                </label>
                <label class="btn btn-sm btn-success <?php if(isset($new_lead_timing) && $new_lead_timing == 'Within a month'): ?> active<?php endif; ?>">
                    <input type="radio" name="new_lead_timing" <?php if(isset($new_lead_timing) && $new_lead_timing == 'Within a month'): ?>checked="checked"<?php endif; ?>  value="Within a month"><i class="fa fa-check text-active"></i> Within a month
                </label>
                <label class="btn btn-sm btn-primary <?php if(isset($new_lead_timing) && $new_lead_timing == 'Not in a Rush'): ?> active<?php endif; ?>">
                    <input type="radio" name="new_lead_timing" <?php if(isset($new_lead_timing) && $new_lead_timing == 'Not in a Rush'): ?>checked="checked"<?php endif; ?>  value="Not in a Rush"><i class="fa fa-check text-active"></i> Not in a Rush
                </label>
            </div>
        </div>
    </div>
</div>

<div class="p-10">
    <div class="control-group row">
        <div class="col-md-6 col-md-push-6">
            <?php $this->load->view('clients/appointment/new_appointment_block'); ?>
        </div>
        <div class="col-md-6 col-md-pull-6">
            <div class="control-group">
                <label class="checkbox m-top-5 m-bottom-5">
                    <input type="checkbox" class="call_lead" name="lead_call">Call The Client
                </label>
            </div>
            <div class="control-group pos-rlt">
                <label class="checkbox m-top-5 m-bottom-5 another-lead-address">
                    <input type="checkbox" name="new_add" id="new_add" value="1" onchange='showOrHide("new_add", "new_add_tab", false, "defaultAddress")'>
                    Another Address
                </label>

                <label class="control-label"><strong>Project Address:</strong></label>
                <div class="controls pos-rlt" id="defaultAddress">
                    <div class="select-lead-address-single hidden">
                        <input type="text" value="<?php echo $client_data->client_address . ', ' . $client_data->client_city; ?>" class="w-100 p-5" disabled>
                    </div>
                    <div class="select-lead-address-multi hidden">
                        <input type="hidden" autocomplete="false" class="select_lead_address w-100" value=""
                               data-value="<?php echo $client_data->client_address; ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="new_add_tab" style="display:none;">
        <hr class="m-top-10 m-bottom-10">
        <div class="row">
            <div class="col-lg-6 col-sm-6 col-xs-12 m-top-5">
                <label class="control-label">Address:</label>
                <input type="text" data-autocompleate="true" data-part-address="address"
                       class="form-control" name="new_address" value="<?php echo $client_data->client_address; ?>">
            </div>
            <div class="col-lg-6 col-sm-6 col-xs-12 m-top-5">
                <label class="control-label">City:</label>
                <input type="text" data-part-address="locality" class="form-control" name="new_city"
                       value="<?php echo $client_data->client_city; ?>" autocomplete="off">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-sm-4 m-top-5">
                <label class="control-label">State/Province:</label>
                <input type="text" name="new_state" class="form-control" autocomplete="off"
                       data-part-address="administrative_area_level_1" value="<?php echo $client_data->client_state; ?>">
            </div>
            <div class="col-lg-4 col-sm-4 m-top-5">
                <label class="control-label">Zip/Postal:</label>
                <input type="text" name="new_zip" class="form-control"
                       value="<?php echo $client_data->client_zip; ?>" data-part-address="postal_code">
            </div>
            <div class="col-lg-4 col-sm-4 m-top-5">
                <label class="control-label">Add. Info:</label>
                <input type="hidden" name="new_country" class="form-control"
                       value="<?php echo $client_data->client_country; ?>" data-part-address="country">
                <input type="text" name="stump_add_info" class="form-control" value="<?php echo $client_data->client_main_intersection; ?>">
                <input type="hidden"  data-part-address="lat" name="new_lat">
                <input type="hidden" data-part-address="lon" name="new_lon">
            </div>
        </div>
    </div>
</div>
<div class="p-10">
    <div class="row">
        <div class="col-md-12 m-t-sm">
            <label><strong>Files:</strong></label><br>
            <div class="dropzone dropzone-lead"></div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <?php echo form_submit('submit', 'Add new lead', "class='btn btn-info'"); ?>
</div>
