<div id="service-<?php echo $service->service_id; ?>" class="modal modal-service fade" tabindex="-1"
     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading">Edit
                Service <?php echo $service->service_name; ?></header>
            <div class="modal-body">
                <div class="form-horizontal">
                    <?php /*
                    <div class="control-group">
                        <label class="control-label">Service Parent Name</label>

                        <div class="controls">
                            <select name="service_parent" class="service_parent form-control">
                                <option value=""> - </option>
                                <?php foreach ($services as $value) : ?>
                                    <?php if($value->service_id != $service->service_id) : ?>
                                        <option value="<?php echo $value->service_id?>">
                                            <?php echo $value->service_name; ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    */ ?>
                    <div class="control-group">
                        <label class="control-label">Service Name</label>

                        <div class="controls">
                            <input class="service_name form-control" type="text"
                                   value="<?php echo htmlentities($service->service_name); ?>"
                                   placeholder="Service Name">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Default Description</label>

                        <div class="controls">
													<textarea class="service_description form-control"
                                                              placeholder="Default Description"
                                                              rows="5"><?php echo $service->service_description; ?></textarea>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Markup (%)</label>

                        <div class="controls">
                            <input class="service_markup form-control percentage"
                                   placeholder="Markup"
                                   value="<?php echo $service->service_markup; ?>">
                        </div>
                    </div>
                    <div class="control-group parentCategory">
                        <label class="control-label">Category</label>
                        <div class="controls">
                            <input type="text" class="parentCategorySelect w-100" value="<?= !empty($service->service_category_id) ? $service->service_category_id : 1 ?>" name="categoryId"/>
                        </div>
                    </div>
                    <div class="control-group parentClass">
                        <label class="control-label">Class</label>
                        <div class="controls">
                            <input type="text" class="parentClassSelect w-100" value="<?= !empty($service->service_class_id) ? $service->service_class_id : null ?>" name="classId"/>
                        </div>
                    </div>
                    <div class="control-group ">
                        <label class="control-label">Crew</label>
                        <div class="controls">
                            <select class="crewsSelect w-100" data-service_id="<?php echo $service->service_id; ?>">
                                <option></option>
                                <?php foreach ($crews as $crew) : ?>
                                    <option value="<?= $crew->crew_id ?>" ><?= $crew->crew_name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="selectedCrew">
                            <?php if(!empty($service->service_default_crews)): ?>
                                <?php foreach (json_decode($service->service_default_crews) as $default_crew) : ?>
                                    <?php foreach ($crews as $crew) :
                                        if($default_crew == $crew->crew_id): ?>
                                            <label class="btn btn-success inline m-r-xs" data-service_id="<?= $service->service_id ?>" data-id="<?= $crew->crew_id ?>" style="padding: 3px 5px;"><?= $crew->crew_name ?><a href="#" class="moveFromCrew">x</a><input type="hidden" "></label>
                            <?php endif; endforeach;  endforeach; endif;?>
                        </div>
                        <input type="hidden" name="crews" class="crews" value='<?= $service->service_default_crews ?>'>
                    </div>
                    <?php if($service->service_attachments) : ?>
                        <div class="serviceSetupList">
                            <?php $attach = json_decode($service->service_attachments);   ?>
                            <?php foreach($attach as $k=>$v) :
                                    if(empty($v)) continue;?>
                                <div class="row m-b-sm pos-rlt">
                                    <div class="serviceSetupTpl">
                                        <div class="control-group col-md-6" >
                                            <label class="control-label">Service Equipment</label>

                                            <div class="controls">
                                                <select name="service_vehicle[]" class="service_vehicle form-control">
                                                    <option value="0"> - </option>
                                                    <?php $selOpt = ''; ?>
                                                    <?php $options = []; ?>
                                                    <?php if(isset($v->vehicle_option) && $v->vehicle_option != '') : ?>
                                                        <?php $selOpt = $v->vehicle_option;?>
                                                    <?php endif; ?>
                                                    <?php foreach ($vehicles as $veh) : ?>
                                                        <option value="<?php echo $veh->vehicle_id; ?>" data-options='<?php echo htmlentities($veh->vehicle_options, ENT_QUOTES, 'UTF-8'); ?>' <?php if(intval($v->vehicle_id) == $veh->vehicle_id) : $options = $veh->vehicle_options; ?>selected="selected"<?php endif; ?>>

                                                            <?php echo $veh->vehicle_name; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <select name="vehicle_option[]" class="vehicle_option form-control">
                                                    <option value=""> None of the options </option>
                                                    <?php if(!empty($options)) : ?>
                                                        <?php foreach (json_decode($options) as $opt) : ?>
                                                            <option value="<?php echo htmlentities($opt, ENT_QUOTES, 'UTF-8'); ?>" <?php if(trim($opt) == $selOpt) :  ?>selected="selected"<?php endif; ?>>
                                                                <?php echo $opt; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group  col-md-6" >
                                            <label class="control-label">Service Attachment</label>

                                            <div class="controls">
                                                <?php $selOpt = ''; ?>
                                                <?php $traiOptions = []; ?>
                                                <?php if(isset($v->trailer_option) && $v->trailer_option != '') : ?>
                                                    <?php $selOpt = $v->trailer_option;?>
                                                <?php endif; ?>
                                                <select name="service_trailer[]" class="service_trailer form-control">
                                                    <option value="0"> - </option>
                                                    <?php foreach ($trailers as $trai) : ?>
                                                        <option value="<?php echo $trai->vehicle_id?>" data-traioptions='<?php echo htmlentities($trai->vehicle_options, ENT_QUOTES, 'UTF-8');?>' <?php if($v->trailer_id == $trai->vehicle_id) : $traiOptions = $trai->vehicle_options;?>selected="selected"<?php endif; ?>>
                                                            <?php echo $trai->vehicle_name; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <select name="trailer_option[]" class="trailer_option form-control">
                                                    <option value=""> None of the options </option>
                                                    <?php if(!empty($traiOptions)) : ?>
                                                        <?php foreach (json_decode($traiOptions) as $opt) : ?>
                                                            <option value="<?php echo htmlentities($opt, ENT_QUOTES, 'UTF-8'); ?>" <?php if(trim($opt) == $selOpt) :  ?>selected="selected"<?php endif; ?>>
                                                                <?php echo $opt; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group col-md-12">
                                            <label class="control-label">Service Tools</label>

                                            <div class="controls">
                                                <?php foreach ($tools as $key=>$tool) : ?>
                                                    <?php $tool_id = FALSE; ?>
                                                    <?php $opt_id = FALSE; ?>
                                                    <?php if(isset($v->tool_id)) : ?>
                                                        <?php $tool_id = array_search($tool->vehicle_id, $v->tool_id); ?>
                                                    <?php endif; ?>
                                                    <?php if($key%3==0) : ?><div class="row"><br><?php endif; ?>
                                                    <div class="col-md-4">
                                                        <strong><?php echo $tool->vehicle_name; ?></strong>
                                                        <?php if($tool->vehicle_options && !empty(json_decode($tool->vehicle_options))) : ?>
                                                            <?php foreach(json_decode($tool->vehicle_options) as $j=>$opt) : ?>
                                                                <?php $opt_id = FALSE; ?>
                                                                <?php if($tool_id !== FALSE) : ?>
                                                                    <?php $opt_id = array_search($opt, $v->tools_option[$tool_id]); ?>
                                                                <?php endif; ?>
                                                                <div class="radio p-left-0">
                                                                    <label>
                                                                        <input type="checkbox" class="tools_option" data-tool_id="<?php echo $tool->vehicle_id; ?>" <?php if($opt_id !== false) : ?>checked<?php endif; ?> name="tools_option[<?php echo $tool->vehicle_id; ?>][<?php echo $k; ?>][]" value="<?php echo htmlentities($opt, ENT_QUOTES, 'UTF-8'); ?>">
                                                                        <font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php echo $opt; ?></font></font>
                                                                    </label>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php else : ?>
                                                            <div class="radio p-left-0">
                                                                <label>
                                                                    <input type="checkbox" class="tools_option" data-tool_id="<?php echo $tool->vehicle_id; ?>" name="tools_option[<?php echo $tool->vehicle_id; ?>][<?php echo $k; ?>][]" value="Any">
                                                                    <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Any</font></font>
                                                                </label>
                                                            </div>
                                                        <?php endif; ?>

                                                    </div>
                                                    <?php if($key%3 == 2 || !isset($tools[$key+1])) : ?></div><?php endif; ?>
                                                <?php endforeach; ?>
                                                <?php /* $selOpt = ''; ?>
																		<?php $toolOptions = []; ?>
																		<?php if(isset($v->tools_option) && $v->tools_option != '') : ?>
																			<?php $selOpt = $v->tools_option;?>
																		<?php endif; ?>
																		<select name="service_tools[]" class="service_tools form-control">
																			<option value=""> - </option>
																			<?php foreach ($tools as $tool) : ?>
																				<option value="<?php echo $tool->vehicle_id?>" data-tooloptions='<?php echo htmlentities($tool->vehicle_options, ENT_QUOTES, 'UTF-8');?>'  <?php if(isset($v->tool_id) && $v->tool_id == $tool->vehicle_id) : $toolOptions = $tool->vehicle_options;?>selected="selected"<?php endif; ?>>
																					<?php echo $tool->vehicle_name; ?>
																				</option>
																			<?php endforeach; ?>
																		</select>
																		<select name="tools_option[]" class="tools_option form-control">
																			<option value=""> None options </option>
																			<?php if(count($toolOptions)) : ?>
																				<?php foreach (json_decode($toolOptions) as $opt) : ?>
																					<option value="<?php echo $opt; ?>" <?php if(trim($opt) == $selOpt) :  ?>selected="selected"<?php endif; ?>>
																						<?php echo $opt; ?>
																					</option>
																				<?php endforeach; ?>
																			<?php endif; ?>
																		</select>
																		*/?>
                                            </div>
                                        </div>
                                        <div class="control-group pos-abt" style="bottom: 10px;right: 50px;<?php if(!$k) : ?>display:none;<?php endif; ?>">
                                            <a class="btn btn-xs btn-danger removeAttach"><i class="fa fa-trash-o"></i></a>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="serviceSetupList">
                            <div class="row m-b-sm pos-rlt ">
                                <div class="serviceSetupTpl">
                                    <div class="control-group col-md-6">
                                        <label class="control-label">Service Equipment</label>

                                        <div class="controls">
                                            <select name="service_vehicle[]" class="service_vehicle form-control">
                                                <option value="0"> - </option>
                                                <?php foreach ($vehicles as $veh) : ?>
                                                    <option value="<?php echo $veh->vehicle_id?>" data-options='<?php echo htmlentities($veh->vehicle_options, ENT_QUOTES, 'UTF-8'); ?>' >
                                                        <?php echo $veh->vehicle_name; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select name="vehicle_option[]" class="vehicle_option form-control">
                                                <option value=""> None of the options </option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="control-group col-md-6">
                                        <label class="control-label">Service Attachment</label>

                                        <div class="controls">
                                            <select name="service_trailer[]" class="service_trailer form-control">
                                                <option value="0"> - </option>
                                                <?php foreach ($trailers as $trai) : ?>
                                                    <option value="<?php echo $trai->vehicle_id?>" data-traioptions='<?php echo htmlentities($trai->vehicle_options, ENT_QUOTES, 'UTF-8');?>' >
                                                        <?php echo $trai->vehicle_name; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select name="trailer_option[]" class="trailer_option form-control">
                                                <option value=""> None of the options </option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="control-group col-md-12">
                                        <label class="control-label">Service Tools</label>

                                        <div class="controls">
                                            <?php foreach ($tools as $k=>$tool) : ?>
                                                <?php if($k%3==0) : ?><div class="row"><br><?php endif; ?>
                                                <div class="col-md-4">
                                                    <strong><?php echo $tool->vehicle_name; ?></strong>
                                                    <?php if($tool->vehicle_options && !empty(json_decode($tool->vehicle_options))) : ?>
                                                        <?php foreach(json_decode($tool->vehicle_options) as $j=>$opt) : ?>
                                                            <div class="radio p-left-0">
                                                                <label>
                                                                    <input type="checkbox" class="tools_option" data-tool_id="<?php echo $tool->vehicle_id; ?>" name="tools_option[<?php echo $tool->vehicle_id; ?>][0][]" value="<?php echo htmlentities($opt, ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php echo $opt; ?></font></font>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php else : ?>
                                                        <div class="radio p-left-0">
                                                            <label>
                                                                <input type="checkbox" class="tools_option" data-tool_id="<?php echo $tool->vehicle_id; ?>" name="tools_option[<?php echo $tool->vehicle_id; ?>][0][]" value="Any">
                                                                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Any</font></font>
                                                            </label>
                                                        </div>
                                                    <?php endif; ?>

                                                </div>
                                                <?php if($k%3 == 2 || !isset($tools[$k+1])) : ?></div><?php endif; ?>
                                            <?php endforeach; ?>
                                            <?php /*
																	<select name="service_tools[]" class="service_tools form-control">
																		<option value=""> - </option>
																		<?php foreach ($tools as $tool) : ?>
																			<option value="<?php echo $tool->vehicle_id; ?>" data-tooloptions='<?php echo $tool->vehicle_options;?>'>
																				<?php echo $tool->vehicle_name; ?>
																			</option>
																		<?php endforeach; ?>
																	</select>
																	<select name="tools_option[]" class="tools_option form-control">
																		<option value=""> None options </option>

																	</select>
																	*/ ?>
                                        </div>
                                    </div>
                                    <div class="control-group pos-abt" style="bottom: 10px;right: 50px;display:none;">
                                        <a class="btn btn-xs btn-danger removeAttach"><i class="fa fa-trash-o"></i></a>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="control-group">
                        <div class="controls">
                            <label class="control-label">
                                <input type="checkbox" name="service_collapsed_view" class="service-collapsed-view" <?php if(empty($service) || !empty($service) && $service->service_is_collapsed == 1) {echo 'checked';} ?>> Collapsed view
                            </label>
                        </div>
                    </div>
                    <?php $this->load->view('partials/is_favourite', ['item' => $service, 'type' => 'service']); ?>
                    <button class="btn btn-xs btn-success pull-right pos-abt addAttach" style="bottom: 0px;right: 55px;" role="button"><i class="fa fa-plus"></i></button>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success"
                        data-save-service="<?php echo $service->service_id; ?>">
                    <span class="btntext">Save</span>
                    <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
                         style="display: none;width: 32px;" class="preloader">
                </button>
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
    </div>
</div>
