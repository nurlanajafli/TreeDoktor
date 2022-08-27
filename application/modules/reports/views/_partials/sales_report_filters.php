<section class="col-lg-3">
    <style>
        .reset, .resetAll, .resetDate {
            cursor: pointer;
            text-decoration: underline;
        }
    </style>
    <section class="panel panel-default p-n">
        <header class="panel-heading">Filter<small class="pull-right resetAll reset-button">Reset All</small></header>
        <div class="table-responsive">
            <form name="search" id="search" method="post" data-type="ajax" data-url="ajax_get_sales_data" data-callback="Sales.render">
                <table class="table m-n">

                    <tr>
                        <td>
                            <small>Select Type</small>
                            <select name="search_client_type" class="input-sm form-control" >
                                <option value="">Client Type</option>
                                <option <?php if(isset($search_client_type) && $search_client_type == 1) : ?>selected="selected"<?php endif; ?> value="1">Residential</option>
                                <option <?php if(isset($search_client_type) && $search_client_type == 2) : ?>selected="selected"<?php endif; ?> value="2">Corporate</option>
                                <option <?php if(isset($search_client_type) && $search_client_type == 3) : ?>selected="selected"<?php endif; ?> value="3">Municipal</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <small>Select Client Tags</small>
                            <div class="row">
                                <div class="control-group col-md-12">
                                    <div class="client-tags-container m-bottom-5">
                                        <div id="folowup-settings-tags-form">
                                            <input class="w-100 js-tags-select2" type="text" name="search_client_tags" multiple="multiple" autocomplete="nope" />
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><small>Select Entity</small><small class="pull-right reset mainReset reset-button">Reset</small>
                            <select name="search_by" class="input-sm form-control" style="overflow: auto;">
                                <option value="">Select Entity</option>
                                <option <?php if ((isset($search_by) && !empty($search_by)) && array_search('estimates', $search_by) !== FALSE) : ?>selected="selected"<?php endif; ?> value="estimates">Estimates</option>
                                <option <?php if ((isset($search_by) && !empty($search_by)) && array_search('workorders', $search_by) !== FALSE) : ?>selected="selected"<?php endif; ?>value="workorders">Workorders</option>
                                <option <?php if ((isset($search_by) && !empty($search_by)) && array_search('invoices', $search_by) !== FALSE) : ?>selected="selected"<?php endif; ?> value="invoices">Invoices</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="hide" id="estimatorSelect">
                        <td><small>Select Estimator</small>
                            <select name="search_estimator" class="input-sm form-control" >
                                <option value="">Select Estimator</option>
                                <?php foreach($estimators as $k=>$v) : ?>
                                    <option <?php if (isset($search_estimator) && $search_estimator == $v['id']): ?>selected="selected"<?php endif; ?> value="<?php echo $v['id'];?>"><?php echo $v['firstname'];?> <?php echo $v['lastname'];?></option>
                                <?php endforeach; ?>
                            </select>

                        </td>
                    </tr>
                </table>
                <table class="table m-n estimatesTable <?php if (!isset($search_by) || empty($search_by) || array_search('estimates', $search_by) === FALSE) : ?>hide<?php endif; ?>">
                    <tr><td><strong>Estimates</strong></td></tr>
                    <tr>
                        <td>
							<div>
								<small>Estimate Date Created</small>
								<div class="pull-left reportrange" style="background: #fff; cursor: pointer; padding: 7px 10px; border: 1px solid #ccc; margin-right: -11px; width:80%; text-align: center;"
									data-dates="<?php echo isset($dates) ? htmlspecialchars(json_encode($dates)) : ''; ?>" >
									<i class="fa fa-calendar"></i>&nbsp;
									<span></span> <i class="fa fa-caret-down"></i>
									<input type="hidden" name="search_estimate_date" value="">
								</div>
								<a class="btn btn-default pull-right resetDate reset-button" style="width: 20%;">Reset</a>
							</div>
                        </td>
                    </tr>
                    <tr>
                        <td><small>Select Estimate Status</small><small class="pull-right reset reset-button" >Reset</small>
                            <select name="search_status[]" multiple="" class="input-sm form-control" >
                                <?php foreach($estimate_statuses as $k=>$v) : ?>
                                    <option <?php if ((isset($search_status) && !empty($search_status)) && array_search($v->est_status_id, $search_status) !== FALSE) : ?>selected="selected"<?php endif; ?> value="<?php echo $v->est_status_id;?>"><?php echo $v->est_status_name;?></option>
                                <?php endforeach; ?>
                            </select>

                        </td>
                    </tr>
                    <tr>
                        <td><small>Select Service</small><small class="pull-right reset reset-button" >Reset</small>
                            <select multiple="" name="search_service_type[]" class="input-sm form-control" >
                                <?php foreach($services as $k=>$v) : ?>
                                    <option <?php if ((isset($search_service_type) && !empty($search_service_type)) && array_search($v->service_id, $search_service_type) !== FALSE) : ?>selected="selected"<?php endif; ?> value="<?php echo $v->service_id;?>"><?php echo $v->service_name;?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><small>Select Product</small><small class="pull-right reset reset-button" >Reset</small>
                            <select multiple="" name="search_service_type[]" class="input-sm form-control" >
                                <?php foreach($products as $k=>$v) : ?>
                                    <option <?php if ((isset($search_service_type) && !empty($search_service_type)) && array_search($v->service_id, $search_service_type) !== FALSE) : ?>selected="selected"<?php endif; ?> value="<?php echo $v->service_id;?>"><?php echo $v->service_name;?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <table class="table m-n workordersTable <?php if (!isset($search_by) || empty($search_by) || array_search('workorders', $search_by) === FALSE) : ?>hide<?php endif; ?>">
                    <tr><td><strong>Workorders</strong></td></tr>
                    <tr>
                        <td>
                            <div>
								<small>Workorder Date Created</small>
								<div class="pull-left reportrange" style="background: #fff; cursor: pointer; padding: 7px 10px; border: 1px solid #ccc; margin-right: -11px; width:80%; text-align: center;"
									data-dates="<?php echo isset($dates) ? htmlspecialchars(json_encode($dates)) : ''; ?>" >
									<i class="fa fa-calendar"></i>&nbsp;
									<span></span> <i class="fa fa-caret-down"></i>
									<input type="hidden" name="search_workorder_date" value="">
								</div>
								<a class="btn btn-default pull-right resetDate reset-button" style="width: 20%;">Reset</a>
							</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <small>Select Status</small><small class="pull-right reset reset-button" >Reset</small>
                            <select name="search_workorder_status[]" multiple="" class="input-sm form-control" >
                                <?php foreach($wo_statuses as $k=>$v) : ?>
                                    <option <?php if ((isset($search_workorder_status) && !empty($search_workorder_status)) && array_search($v['wo_status_id'], $search_workorder_status) !== FALSE) : ?>selected="selected"<?php endif; ?> value="<?php echo $v['wo_status_id'];?>"><?php echo $v['wo_status_name'];?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><small>Select Service</small><small class="pull-right reset reset-button" >Reset</small>
                            <select multiple="" name="search_service_type[]" class="input-sm form-control" >
                                <?php foreach($services as $k=>$v) : ?>
                                    <option <?php if ((isset($search_service_type) && !empty($search_service_type)) && array_search($v->service_id, $search_service_type) !== FALSE) : ?>selected="selected"<?php endif; ?> value="<?php echo $v->service_id;?>"><?php echo $v->service_name;?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><small>Select Product</small><small class="pull-right reset reset-button" >Reset</small>
                            <select multiple="" name="search_service_type[]" class="input-sm form-control" >
                                <?php foreach($products as $k=>$v) : ?>
                                    <option <?php if ((isset($search_service_type) && !empty($search_service_type)) && array_search($v->service_id, $search_service_type) !== FALSE) : ?>selected="selected"<?php endif; ?> value="<?php echo $v->service_id;?>"><?php echo $v->service_name;?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <table class="table m-n invoicesTable <?php if (!isset($search_by) || empty($search_by) || array_search('invoices', $search_by) === FALSE) : ?>hide <?php endif; ?>">
                    <tr><td><strong>Invoices</strong></td></tr>
                    <tr>
                        <td>
                            <div>
								<small class="block">Invoice Date Created</small>
								<div class="pull-left reportrange" style="background: #fff; cursor: pointer; padding: 7px 10px; border: 1px solid #ccc; margin-right: -11px; width:80%; text-align: center;"
									data-dates="<?php echo isset($dates) ? htmlspecialchars(json_encode($dates)) : ''; ?>" >
									<i class="fa fa-calendar"></i>&nbsp;
									<span></span> <i class="fa fa-caret-down"></i>
									<input type="hidden" name="search_invoice_date" value="">
								</div>
								<a class="btn btn-default pull-right resetDate reset-button" style="width: 20%;">Reset</a>
							</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <small>Select Status</small><small class="pull-right reset reset-button" >Reset</small>
                            <select name="search_invoice_status[]" multiple="" class="input-sm form-control" >
                                <?php if(isset($invoices_statuses) && !empty($invoices_statuses)) : ?>
                                    <?php foreach($invoices_statuses as $k=>$v) : ?>
                                        <option <?php if ((isset($search_invoice_status) && !empty($search_invoice_status)) && array_search(intval($v->invoice_status_id), $search_invoice_status) !== FALSE) : ?>selected="selected"<?php endif; ?> value="<?php echo $v->invoice_status_id; ?>"><?php echo $v->invoice_status_name; ?></option>
                                    <?php endforeach;?>
                                <?php endif;?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><small>Select Service</small><small class="pull-right reset reset-button" >Reset</small>
                            <select multiple="" name="search_service_type[]" class="input-sm form-control" >
                                <?php foreach($services as $k=>$v) : ?>
                                    <option <?php if ((isset($search_service_type) && !empty($search_service_type)) && array_search($v->service_id, $search_service_type) !== FALSE) : ?>selected="selected"<?php endif; ?> value="<?php echo $v->service_id;?>"><?php echo $v->service_name;?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><small>Select Product</small><small class="pull-right reset reset-button" >Reset</small>
                            <select multiple="" name="search_service_type[]" class="input-sm form-control" >
                                <?php foreach($products as $k=>$v) : ?>
                                    <option <?php if ((isset($search_service_type) && !empty($search_service_type)) && array_search($v->service_id, $search_service_type) !== FALSE) : ?>selected="selected"<?php endif; ?> value="<?php echo $v->service_id;?>"><?php echo $v->service_name;?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <div class="text-center" style="padding: 6px 15px;">
                    <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
                    <input type="hidden" id="offsetValue" name="offset" value="0" />
                    <button class="btn btn-sm btn-default" style="width:100%; background-color: #ededed;" disabled="disabled" type="submit" id="searchEst">Go!</button>
                </div>
            </form>
        </div>

    </section>
</section>
