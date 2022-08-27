<div class="modal fade" id="copyEstimateForm-modal" tabindex="-1" role="dialog" aria-hidden="true"
     style="z-index: 9998;">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">Copy this estimate</h4>
            </div>
            <div class="modal-body p-bottom-0" id="copyEstimateForm-modal-body">
                <form class="form-horizontal pt-3" action="">
                    <div class="copyEstimateForm-result">
                        <div class="form-group ">
                            <label class="col-sm-3 control-label">Client:</label>
                            <div class="col-sm-9">
                                <select name="select_copy_client" class="form-control">
                                    <option value="1" selected>
                                        <?php
                                            $infoAboutClient[]=$client_info->cc_name;
                                            if (!empty($client_info->cc_phone) )
                                                $infoAboutClient[]=numberTo($client_info->cc_phone);
                                            if (!empty($client_info->cc_email) )
                                                $infoAboutClient[]=$client_info->cc_email;
                                            echo implode(', ', $infoAboutClient).'.';
                                        ?>
                                    </option>
                                    <option value="2">Other client</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group select_other_client hidden">
                            <label class="col-sm-3 control-label">Other client:</label>
                            <div class="col-sm-9">
                                <input type="text" name="new_client_id" id="new_client_id" value=""
                                       style="width:100%;"/>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="radio">
                                            <label class="radio-custom">
                                                <input type="radio" name="to_status" value="estimate" checked="checked">
                                                <i class="fa fa-circle-o checked"></i>
                                                Estimate
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="radio">
                                            <label class="radio-custom <?php if(!isset($workorder_data)) : ?> text-muted <?php endif; ?>">
                                                <input type="radio" name="to_status" value="workorders" <?php if(!isset($workorder_data)) : ?> disabled="disabled" <?php endif; ?> >
                                                <i class="fa fa-circle-o <?php if(!isset($workorder_data)) : ?> disabled <?php endif; ?>>"></i>
                                                Workorder
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="radio">
                                            <label class="radio-custom <?php if(!isset($invoice_data)) : ?> text-muted <?php endif; ?>">
                                                <input type="radio" name="to_status" value="invoices" <?php if(!isset($invoice_data)) : ?> disabled="disabled" <?php endif; ?>>
                                                <i class="fa fa-circle-o <?php if(!isset($invoice_data)) : ?> disabled <?php endif; ?>>"></i>
                                                Invoice
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group estimate_status group_statuses">
                            <label class="col-sm-3 control-label">Estimate status:</label>
                            <div class="col-sm-9">
                                <select name="est_status" class="form-control">
                                    <?php foreach ($estimateStatuses as $estimate_status) : ?>
                                        <option value="<?php echo $estimate_status['est_status_id']; ?>"
                                            <?php echo $estimate_status['est_status_default'] ? 'selected' : '' ?>
                                        >
                                            <?php echo $estimate_status['est_status_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group workorders_status group_statuses" style="display:none;">
                            <label class="col-sm-3 control-label">Workorders status:</label>
                            <div class="col-sm-9">
                                <select name="wo_status" class="form-control ">
                                    <?php foreach ($workorderStatuses as $wo_status) : ?>
                                        <option value="<?php echo $wo_status['wo_status_id']; ?>"
                                            <?php echo $wo_status['is_default'] ? 'selected' : '' ?>
                                        >
                                            <?php echo $wo_status['wo_status_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group invoices_status group_statuses " style="display:none;">
                            <label class="col-sm-3 control-label">Invoices status:</label>
                            <div class="col-sm-9">
                                <select name="invoices_status" class="form-control ">
                                    <?php foreach ($invoiceStatuses as $invoice_status) : ?>
                                        <option value="<?php echo $invoice_status['invoice_status_id']; ?>"
                                            <?php echo $invoice_status['default'] ? 'selected' : '' ?>
                                        >
                                            <?php echo $invoice_status['invoice_status_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>


                    </div>
                    <input type="hidden" name="estimate_id" value="<?php echo $estimate_data->estimate_id; ?>"/>
                    <div class="form-group text-right mb-3">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <button type="button" class="btn btn-dark close-modalwindow" data-dismiss="modal">Close
                            </button>
                            <button type="button" class="btn btn-success copyEstimate">Create copy</button>
                        </div>
                    </div>
                </form>
                <div style="display:none" class="forPresave">
                    
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>