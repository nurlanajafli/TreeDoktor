<header class="panel-heading">
    {{if wo_status_id != undefined }}
    <i class="fa fa-pencil text-primary"></i>&nbsp;Edit Status {{:status.wo_status_name}}
    {{else}}
    <i class="fa fa-plus text-primary"></i>&nbsp;Create Status
    {{/if}}
</header>
<form data-type="ajax" method="POST" data-location="/workorders/status" data-url="/workorders/status/ajax_save_status" id="save-workorder-status-form">
<div class="modal-body">
    <div class="form-horizontal">
        <div class="control-group m-bottom-10">
            <div class="row">
                <div class="col-lg-4">
                    <label class="control-label"><i class="fa fa-bookmark text-primary"></i>&nbsp;Name: </label>
                </div>
                {{if wo_status_id != undefined }}
                <input type="hidden" name="wo_status_id" value="{{:wo_status_id}}">
                {{/if}}
                <div class="controls col-lg-8">
                    <input name="wo_status_name" class="status_name form-control no-bordered-input" type="text" value="{{if status.wo_status_name!=undefined}}{{:status.wo_status_name}}{{/if}}" placeholder="Crew Name">
                    <span class="form-error"></span>
                </div>
            </div>
        </div>

        <div class="control-group m-bottom-10">
            <div class="row">
                <div class="col-lg-4">
                    <label class="control-label"><i class="fa fa-magic text-primary"></i>&nbsp;Schedule Sticker Color: </label>
                </div>
                <div class="btn-group col-lg-6 col-md-7 col-sm-7 col-xs-7 p-right-0" data-toggle="buttons">

                    <label class="btn btn-sm btn-info bg-white border-0 br-radius-15 {{if status.wo_status_use_team_color==1 || status.wo_status_color==undefined}}active{{/if}} ">
                        <input type="radio" name="status_color_type" id="wo_status_use_team_color" {{if status.wo_status_use_team_color==1 || status.wo_status_color==undefined}}checked{{/if}} value="wo_status_use_team_color"><i class="fa fa-users"></i>&nbsp;Team&nbsp;<i class="fa fa-check text-active"></i>
                    </label>
                    <label class="btn btn-sm btn-success bg-white border-0 br-radius-15 {{if status.wo_status_use_estimator_color==1}}active{{/if}}">
                        <input type="radio" name="status_color_type" id="wo_status_use_estimator_color" {{if status.wo_status_use_estimator_color==1}}checked{{/if}} value="wo_status_use_estimator_color"><i class="fa fa-user"></i>&nbsp;Estimator&nbsp;<i class="fa fa-check text-active"></i>
                    </label>
                    <label class="btn btn-sm btn-primary bg-white border-0 br-radius-15 {{if status.wo_status_use_team_color==0 && status.wo_status_use_estimator_color==0}}active{{/if}}">
                        <input type="radio" name="status_color_type" id="custom_color" {{if status.wo_status_use_team_color==0 && status.wo_status_use_estimator_color==0}}checked{{/if}} value="wo_status_color"><i class="fa fa-plus"></i>&nbsp;Custom&nbsp;<i class="fa fa-check text-active"></i>
                    </label>


                </div>
                <div class="controls col-lg-2 col-md-5 col-sm-5 col-xs-5">
                    <input name="wo_status_color" {{if status.wo_status_use_team_color==1 || status.wo_status_use_estimator_color==1 || status.wo_status_color==undefined}}disabled="disabled"{{/if}} class="mycolorpicker form-control status_color color-input-rounded" type="text"
                           value="{{if status.wo_status_color != undefined}}{{:status.wo_status_color}}{{/if}}"
                           placeholder="Team Color" style="background-color: #fff; margin-top: -3px;">
                </div>
            </div>
        </div>

        {{if status.wo_status_id==undefined || (status.wo_status_id!=undefined && status.is_default==0 && status.is_finished==0)}}
            <hr>
            <div class="extra-status-logic-container">
                <div class="checkbox">
                    <label class="checkbox-custom" for="is_finished_by_field">
                        <input type="checkbox" id="is_finished_by_field" {{if wo_status_id != undefined}}data-id="{{:wo_status_id}}"{{/if}} class="extra-status-logic" value="1" {{if (status.is_protected==1 && status.is_finished_by_field==0) || (exist_extra_status.is_finished_by_field!=undefined && (status.is_finished_by_field==0 || status.is_finished_by_field==undefined))}}disabled="disabled"{{/if}} {{if status.is_finished_by_field==1}}checked="checked"{{/if}}>
                        <input type="hidden" name="is_finished_by_field" value="{{if status.is_finished_by_field==1}}1{{else}}0{{/if}}">
                        <i class="fa fa-fw fa-square-o"></i>
                        Finished by field
                    </label>
                </div>

                <div class="checkbox" for="is_confirm_by_client">
                    <label class="checkbox-custom">
                        <input type="checkbox" id="is_confirm_by_client" {{if wo_status_id != undefined}}data-id="{{:wo_status_id}}"{{/if}} class="extra-status-logic" value="1" {{if (status.is_protected==1 && status.is_confirm_by_client==0) || (exist_extra_status.is_confirm_by_client!=undefined && (status.is_confirm_by_client==0 || status.is_confirm_by_client==undefined))}}disabled="disabled"{{/if}} {{if status.is_confirm_by_client==1}}checked="checked"{{/if}}>
                        <input type="hidden" name="is_confirm_by_client" value="{{if status.is_confirm_by_client==1}}1{{else}}0{{/if}}">
                        <i class="fa fa-fw fa-square-o"></i>
                        Confirmed online
                    </label>
                </div>

                <div class="checkbox">
                    <label class="checkbox-custom" for="is_delete_invoice">
                        <input type="checkbox" id="is_delete_invoice" {{if wo_status_id != undefined}}data-id="{{:wo_status_id}}"{{/if}} class="extra-status-logic" value="1" {{if (status.is_protected==1 && status.is_delete_invoice==0) || (exist_extra_status.is_delete_invoice!=undefined && (status.is_delete_invoice==0 || status.is_delete_invoice==undefined))}}disabled="disabled"{{/if}} {{if status.is_delete_invoice==1}}checked="checked"{{/if}}>
                        <input type="hidden" name="is_delete_invoice" value="{{if status.is_delete_invoice==1}}1{{else}}0{{/if}}">
                        <i class="fa fa-fw fa-square-o"></i>
                        Deleted Invoice
                    </label>
                </div>
                <div class="clear"></div>
            </div>
        {{/if}}
    </div>
</div>

<div class="modal-footer">
    <button class="btn btn-success" type="submit" data-save-status="{{if wo_status_id != undefined}}{{:wo_status_id}}{{/if}}">
        <span class="btntext">Save</span>
        <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;" class="preloader">
    </button>
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
</div>
</form>