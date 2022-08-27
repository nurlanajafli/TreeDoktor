<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/css/colpick.css'); ?>"/>
<script src="<?php echo base_url('assets/js/colpick.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/jasny-bootstrap.js'); ?>"></script>
<style>
    .table.table-hover tr td {
		line-height: 30px;
	}
    .switch.disabled {
        cursor: not-allowed;
    }
    .switch.disabled>span {
        background: #eeeeee;
    }
</style>
<section class="scrollable p-sides-15">
<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
	<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
	<li><a href="<?php echo base_url('user'); ?>">Users</a></li>
	<li class="active"><?php if (isset($user_row->user_id)) : ?>User - <?php echo $user_row->firstname; ?><?php else : ?>New User<?php endif; ?></li>
</ul>

<?php 
$attri = array(
	'name' => 'user_form',
	'id' => 'user_form');

$url = base_url() . 'user/save/';
if (isset($edit))
	$url = base_url() . 'user/save/' . $user_row->id;

?>

<h4 class="m-t-xs">User Profile</h4>

<style type="text/css">
	@media (max-width: 1400px){
		#user_form{zoom:0.8;}
	}
	
	@media (min-width: 992px){
		.picture-dropzone{min-height: 110px!important;}

	}
	@media (min-width: 768px){
		.picture-dropzone{min-height: 110px!important;}	
		.m-sm-bottom-n{ margin-bottom: 0!important; }
	}

	<?php if(is_support((isset($user_row))?$user_row:[])):  ?>
	.picture-dropzone{min-height: 138px!important;}
	@media (min-width: 1200px){
		.picture-dropzone{min-height: 138px!important;}
		textarea[rows="6"]{ padding-bottom: 6px; }
		.m-lg-bottom-15{ margin-bottom: 15px; }
	}
	@media (min-width: 992px){
		.picture-dropzone{min-height: 136px!important;}
		textarea[rows="6"]{ padding-bottom: 11px; }
	}
	@media (min-width: 768px){
		.picture-dropzone{min-height: 141px!important;}	
	}
	

	<?php else: ?>
	.picture-dropzone{min-height: 131px!important;}
	@media (min-width: 1200px){
		.picture-dropzone{min-height: 131px!important;}
	}
	<?php endif; 
	?>
	.rule-name--row {
		display: flex;
		flex-direction: row;
	}

	.rule-name--popover {
		font-size: 18px;
		font-weight: 600;
	}

	.rule-name--popover i {
		display: flex;
		justify-content: center;
		align-items: center;
		color: #b8b8b8;
	}

	.rule-name--popover:hover, .rule-name--popover:hover * {
		color: #a2a1a1;
	}

	.rule-name--popover :active, :focus, :focus-visible {
		outline: none!important;
		box-shadow: none!important;
	}

	.control-label {
		overflow: hidden;
    	white-space: nowrap;
	}

	textarea[rows="2"]{ padding-bottom: 11px; }
	
</style>

<form action="<?php echo $url; ?>" method="POST"  id="user_form" name="user_form" enctype="multipart/form-data">

	<div class="row">
		<div class="col-sm-6 col-md-6 col-lg-4">
			<section class="panel panel-default">
	            <header class="panel-heading font-bold">
                    Basic
                    <?php if(isset($edit)) : ?>
                    <div class="pull-right">
                        <a href="#" class="close_all_other_sessions" data-url="<?=base_url() . 'user/clear_user_data/' . $edit ?>">
                            <i class="fa fa-sign-out"></i><span>Terminate all other sessions</span>
                        </a>
                    </div>
                    <?php endif; ?>
                </header>
	            <div class="panel-body">
					<?php $this->load->view('form/basic_form'); ?>
	            </div>
	        </section>
		</div>

		<div class="col-sm-6 col-md-6 col-lg-4">
			<section class="panel panel-default">
	            <header class="panel-heading font-bold">Contact</header>
	            <div class="panel-body">
					<?php $this->load->view('form/additionally_form'); ?>
	            </div>
	        </section>
		</div>

		<div class="col-sm-6 col-md-6 col-lg-4">
			<section class="panel panel-default">
	            <header class="panel-heading font-bold">Payroll Details and Rules</header>
	            <div class="panel-body">
				<?php $this->load->view('form/employee_form');?>
				</div>
			</section>
		</div>

		<div class="col-sm-6 col-md-6 col-lg-12">
		<?php $this->load->view('form/docs_form');?>
		</div>
	</div>


    <?php if ($this->session->userdata('user_type') == "admin") : ?>
	<section class="panel panel-default options-section" <?php /*if(!is_support((isset($user_row))?$user_row:[] )):  ?>style="display:none;"<?php endif;*/ ?>>
        <header class="panel-heading font-bold">Settings</header>
        <div class="panel-body">
			<table class="table table-hover" >

			<tr>
				<th>Rule Name</th>
				<th>Access</th>
			</tr>
			<?php
            $readOnly = 0;
			if (isset($module_opt) && !empty($module_opt)) {
				foreach ($module_opt as $row) {
					if (isset($user_module) && !empty($user_module)) {

						$module_status = '';
						foreach ($user_module as $module) {
                            if($user_row->user_type === 'admin' && $row->module_id !== 'GPS') {
                                $module_status = 1;
                                $readOnly = true;
                                break;
                            }
							if ($module->module_id == $row->module_id) {
								$module_status = $module->module_status;
								break;
							} else {
								$module_status = $this->input->post($module->module_id);
							}
						}
					} else {
						$module_status = $this->input->post($row->module_id);
					}
					if($row->module_id == 'TLCSTE') {
                        ?>
                        <tr>
                            <td class="rule-name--row"><?php echo $row->module_desc; ?> Access
								<div class="rule-name--popover-container">
									<span class="rule-name--popover btn btn-rounded" tabindex="0" data-html="true" data-toggle="popover" title="Editing team members work time" role="button" data-trigger="focus"
										data-content="<p>The team lead can add or edit the work time of the team members on the Arbostar App.</p>">
										<i class="fa fa-question-circle" aria-hidden="true"></i>
									</span>
								</div>
							</td>
                            <td>
                                <?php

                                if (set_value($row->module_id) == '1' || @$module_status == '1') {
                                    $checked = TRUE;
                                } else {
                                    $checked = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_1',
                                    'class' => $row->module_id,
                                    'value' => '1',
                                    'checked' => $checked,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                Yes
                                <?php if (set_value($row->module_id) == '0' || @$module_status == '0' || @$module_status == '') {
                                    $checked1 = TRUE;
                                } else {
                                    $checked1 = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_0',
                                    'class' => $row->module_id,
                                    'value' => '0',
                                    'checked' => $checked1,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                NO
                                <div id="dcountry"></div>
                            </td>
                        </tr>
                        <?php
                    }
                    else if($row->module_id == 'FWSS') {
                        ?>
                        <tr>
                            <td class="rule-name--row"><?php echo $row->module_desc; ?> Access
                                <div class="rule-name--popover-container">
									<span class="rule-name--popover btn btn-rounded" tabindex="0" data-html="true" data-toggle="popover" title="Editing job service status" role="button" data-trigger="focus"
                                          data-content="<p>Crew leader ability to change service status in job from New to Completed. Available only on the ArboStar App.</p>">
										<i class="fa fa-question-circle" aria-hidden="true"></i>
									</span>
                                </div>
                            </td>
                            <td>
                                <?php

                                if (set_value($row->module_id) == '1' || @$module_status == '1') {
                                    $checked = TRUE;
                                } else {
                                    $checked = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_1',
                                    'class' => $row->module_id,
                                    'value' => '1',
                                    'checked' => $checked,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                Yes
                                <?php if (set_value($row->module_id) == '0' || @$module_status == '0' || @$module_status == '') {
                                    $checked1 = TRUE;
                                } else {
                                    $checked1 = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_0',
                                    'class' => $row->module_id,
                                    'value' => '0',
                                    'checked' => $checked1,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                NO
                                <div id="dcountry"></div>
                            </td>
                        </tr>
                        <?php
                    }
                    else if($row->module_id == 'FWI') {
                        ?>
                        <tr>
                            <td class="rule-name--row"><?php echo $row->module_desc; ?> Access
                                <div class="rule-name--popover-container">
									<span class="rule-name--popover btn btn-rounded" tabindex="0" data-html="true" data-toggle="popover" title="Send invoice after workorder finished" role="button" data-trigger="focus"
                                          data-content="<p>When all Job services in the Workorder has the completed status, the crew leader has the ability to send an invoice to the client. Available only on the ArboStar App.</p>">
										<i class="fa fa-question-circle" aria-hidden="true"></i>
									</span>
                                </div>
                            </td>
                            <td>
                                <?php

                                if (set_value($row->module_id) == '1' || @$module_status == '1') {
                                    $checked = TRUE;
                                } else {
                                    $checked = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_1',
                                    'class' => $row->module_id,
                                    'value' => '1',
                                    'checked' => $checked,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                Yes
                                <?php if (set_value($row->module_id) == '0' || @$module_status == '0' || @$module_status == '') {
                                    $checked1 = TRUE;
                                } else {
                                    $checked1 = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_0',
                                    'class' => $row->module_id,
                                    'value' => '0',
                                    'checked' => $checked1,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                NO
                                <div id="dcountry"></div>
                            </td>
                        </tr>
                        <?php
                    }
                    else if($row->module_id == 'GPS') {
                        ?>
                        <tr>
                            <td class="rule-name--row"><?php echo $row->module_desc; ?> Access
                                <div class="rule-name--popover-container">
									<span class="rule-name--popover btn btn-rounded" tabindex="0" data-html="true" data-toggle="popover" title="Require GPS to track time" role="button" data-trigger="focus"
                                          data-content="<p>Allow tracking time without using GPS. Available only on the ArboStar App.</p>">
										<i class="fa fa-question-circle" aria-hidden="true"></i>
									</span>
                                </div>
                            </td>
                            <td class="pos-rlt">
                                <?php

                                if (set_value($row->module_id) == '1' || @$module_status == '1') {
                                    $checked = TRUE;
                                } else {
                                    $checked = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_1',
                                    'class' => $row->module_id,
                                    'value' => '1',
                                    'checked' => $checked,
                                    'style' => 'margin:10px');

                                echo form_radio($data); ?>
                                Yes
                                <?php if (set_value($row->module_id) == '0' || @$module_status == '0' || @$module_status == '') {
                                    $checked1 = TRUE;
                                } else {
                                    $checked1 = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_0',
                                    'class' => $row->module_id,
                                    'value' => '0',
                                    'checked' => $checked1,
                                    'style' => 'margin:10px');
                                echo form_radio($data); ?>
                                NO

                                <div style="left: 150px;top: 0; width: 130px;" class="m-l-md pos-abt">
                                    <div class="pull-left m-r-sm" style="margin-top: 8px;">Tracking</div>
                                    <label class="switch pull-right<?php if($checked1) : ?> disabled<?php endif; ?>" style="height: 0;margin-top: 7px;">
                                        <input type="checkbox" name="is_tracked" value="1" id="is_tracked" <?php if($checked1) : ?> disabled<?php endif; ?>
                                        <?php echo (isset($user_row->is_tracked) && $user_row->is_tracked == 1) ? 'checked="checked"' : FALSE; ?>>
                                        <span></span>
                                    </label>
                                </div>

                            </td>
                        </tr>
                        <?php
                    }
					else if($row->module_id == 'IMP_CT') {
                        ?>
                        <tr>
                            <td><?php echo $row->module_desc; ?> Access</td>
                            <td>
                                <?php

                                if (set_value($row->module_id) == '1' || @$module_status == '1') {
                                    $checked = TRUE;
                                } else {
                                    $checked = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_1',
                                    'class' => $row->module_id,
                                    'value' => '1',
                                    'checked' => $checked,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                Yes
                                <?php if (set_value($row->module_id) == '0' || @$module_status == '0' || @$module_status == '') {
                                    $checked1 = TRUE;
                                } else {
                                    $checked1 = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_0',
                                    'class' => $row->module_id,
                                    'value' => '0',
                                    'checked' => $checked1,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                NO
                                <div id="dcountry"></div>
                            </td>
                        </tr>
                        <?php
                    } elseif ($row->module_id == 'CL') { ?>
                        <tr<?php if(!is_support((isset($user_row))?$user_row:[] )):  ?> style="display:none;"<?php endif; ?>>
                            <td class="rule-name--row"><?php echo $row->module_desc; ?>
								<div class="rule-name--popover-container">
									<span class="rule-name--popover btn btn-rounded" tabindex="0" data-html="true" data-toggle="popover" title="CRM Access Level" role="button" data-trigger="focus"
										data-content="
										<p><b>Full</b> - user has access to all clients, projects (leads, estimates, workorders, invoices) and can create new projects.</p>
										<p><b>Limited</b> - user has access only to their own projects (leads, estimates, workorders, invoices), clients and can create new projects only for the clients created by themselves.</p>
										<p><b>None</b> - user has no access to clients, projects and cannot create their own projects.</p>
										">
										<i class="fa fa-question-circle" aria-hidden="true"></i>
									</span>
								</div>
							</td>
                            <td collspan="4">
                                <?php

                                if (set_value($row->module_id) == '1' || @$module_status == '1') {
                                    $checked = TRUE;
                                } else {
                                    $checked = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_1',
                                    'class' => $row->module_id,
                                    'value' => '1',
                                    'checked' => $checked,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                Full

                                <?php if (set_value($row->module_id) == '2' || @$module_status == '2') {
                                    $checked2 = TRUE;
                                } else {
                                    $checked2 = FALSE;
                                }
                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_2',
                                    'class' => $row->module_id,
                                    'value' => '2',
                                    'checked' => $checked2,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                Limited

                                <?php if (set_value($row->module_id) == '0' || @$module_status == '0' || @$module_status == '') {
                                    $checked1 = TRUE;
                                } else {
                                    $checked1 = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_0',
                                    'class' => $row->module_id,
                                    'value' => '0',
                                    'checked' => $checked1,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                None


                                <div id="dcountry"></div>
                            </td>
                        </tr>
                    <?php } else if ($row->module_id == 'TSKS') { ?>
                        <tr<?php if(!is_support((isset($user_row))?$user_row:[] )):  ?> style="display:none;"<?php endif; ?>>
                            <td><?php echo $row->module_desc; ?> Access</td>
                            <td>
                                <?php

                                if (set_value($row->module_id) == '1' || @$module_status == '1') {
                                    $checked = TRUE;
                                } else {
                                    $checked = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_1',
                                    'class' => $row->module_id,
                                    'value' => '1',
                                    'checked' => $checked,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                Show
                                <?php if (set_value($row->module_id) == '0' || @$module_status == '0' || @$module_status == '') {
                                    $checked1 = TRUE;
                                } else {
                                    $checked1 = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_0',
                                    'class' => $row->module_id,
                                    'value' => '0',
                                    'checked' => $checked1,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                Hide

                                <?php if (set_value($row->module_id) == '2' || @$module_status == '2') {
                                    $checked2 = TRUE;
                                } else {
                                    $checked2 = FALSE;
                                }
                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_2',
                                    'class' => $row->module_id,
                                    'value' => '2',
                                    'checked' => $checked2,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                Own Tasks

                                <div id="dcountry"></div>
                            </td>
                        </tr>
                    <?php } else if ($row->module_id == 'STP') { ?>
                        <tr<?php if(!is_support((isset($user_row))?$user_row:[] )):  ?> style="display:none;"<?php endif; ?>>
                            <td><?php echo $row->module_desc; ?> Access</td>
                            <td>
                                <?php

                                if (set_value($row->module_id) == '2' || @$module_status == '2') {
                                    $checked = TRUE;
                                } else {
                                    $checked = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_2',
                                    'class' => $row->module_id,
                                    'value' => '2',
                                    'checked' => $checked,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                My Stumps
                                <?php

                                if (set_value($row->module_id) == '1' || @$module_status == '1') {
                                    $checked = TRUE;
                                } else {
                                    $checked = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_1',
                                    'class' => $row->module_id,
                                    'value' => '1',
                                    'checked' => $checked,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }

                                echo form_radio($data); ?>
                                All
                                <?php if (set_value($row->module_id) == '0' || @$module_status == '0' || @$module_status == '') {
                                    $checked1 = TRUE;
                                } else {
                                    $checked1 = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_0',
                                    'class' => $row->module_id,
                                    'value' => '0',
                                    'checked' => $checked1,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                No
                                <?php if (set_value($row->module_id) == '3' || @$module_status == '3') {
                                    $checked1 = TRUE;
                                } else {
                                    $checked1 = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_3',
                                    'class' => $row->module_id,
                                    'value' => '3',
                                    'checked' => $checked1,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                Readonly List And Map
                                <div id="dcountry"></div>
                            </td>
                        </tr>
                    <?php } else if ($row->module_id == 'STA') { ?>
                        <tr<?php if(!is_support((isset($user_row))?$user_row:[] )):  ?> style="display:none;"<?php endif; ?>>
                            <td><?php echo $row->module_desc; ?> Access</td>
                            <td>
                                <?php

                                if (set_value($row->module_id) == '1' || @$module_status == '1') {
                                    $checked = TRUE;
                                } else {
                                    $checked = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_1',
                                    'class' => $row->module_id,
                                    'value' => '1',
                                    'checked' => $checked,
                                    'style' => 'margin:10px');

                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }

                                echo form_radio($data); ?>
                                Yes
                                <?php if (set_value($row->module_id) == '0' || @$module_status == '0' || @$module_status == '') {
                                    $checked1 = TRUE;
                                } else {
                                    $checked1 = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_0',
                                    'class' => $row->module_id,
                                    'value' => '0',
                                    'checked' => $checked1,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                No
                                <div id="dcountry"></div>
                            </td>
                        </tr>
                    <?php } else if ($row->module_id == 'AEEPT') { ?>
                        <tr style="display:none" id="TR_AEEPT">
                            <td class="rule-name--row"><?php echo $row->module_desc; ?> Access
                                <div class="rule-name--popover-container">
									<span class="rule-name--popover btn btn-rounded" tabindex="0" data-html="true" data-toggle="popover" title="Allow editing employee payroll times" role="button" data-trigger="focus"
                                          data-content="
                                              <p>Within N days, it will be allowed to edit the payroll time after the payroll is completed.</p>
                                              <p>If this field is empty or equal to 0, you have no limit on the number of days to edit.</p>
                                          ">
                                    <i class="fa fa-question-circle" aria-hidden="true"></i>
									</span>
                                </div>
                            </td>
                            <td class="pos-rlt">
                                <div class="d-flex">
                                    <?php

                                    if (set_value($row->module_id) == '1' || @$module_status == '1') {
                                        $checked = TRUE;
                                    } else {
                                        $checked = FALSE;
                                    }

                                    $data = array(
                                        'name' => $row->module_id,
                                        'id' => $row->module_id . '_1',
                                        'class' => $row->module_id,
                                        'value' => '1',
                                        'checked' => $checked,
                                        'style' => 'margin:10px');

                                    if($readOnly) {
                                        $data['disabled'] = $readOnly;
                                    }

                                    echo form_radio($data); ?>
                                    Yes
                                    <?php if (set_value($row->module_id) == '0' || @$module_status == '0' || @$module_status == '') {
                                        $checked1 = TRUE;
                                    } else {
                                        $checked1 = FALSE;
                                    }

                                    $data = array(
                                        'name' => $row->module_id,
                                        'id' => $row->module_id . '_0',
                                        'class' => $row->module_id,
                                        'value' => '0',
                                        'checked' => $checked1,
                                        'style' => 'margin:10px');
                                    if($readOnly) {
                                        $data['disabled'] = $readOnly;
                                    }
                                    echo form_radio($data); ?>
                                    No

                                    <div class="d-flex p-left-10">
                                        <label>During</label>
                                        <div class="d-flex m-sides-10">
                                            <input class="switch form-control" <?php echo $checked1 ? 'disabled="disabled"' : '' ?> type="number" id="AEEPT_DURING" name="during" value="<?php echo isset($user_row) ? $user_row->during : 0 ?>" min="0" style="width: 70px">
                                        </div>
                                        days
                                    </div>
                                </div>
                            </td>
                        </tr>

                    <?php } else { ?>
                        <tr<?php if(!is_support((isset($user_row))?$user_row:[] )):  ?> style="display:none;"<?php endif; ?>>
                            <td><?php echo $row->module_desc; ?> Access </td>
                            <td> 
                                <?php

                                if (set_value($row->module_id) == '1' || @$module_status == '1') {
                                    $checked = TRUE;
                                } else {
                                    $checked = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_1',
                                    'class' => $row->module_id,
                                    'value' => '1',
                                    'checked' => $checked,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }

                                echo form_radio($data); ?>
                                Yes
                                <?php if (set_value($row->module_id) == '0' || @$module_status == '0' || @$module_status == '') {
                                    $checked1 = TRUE;
                                } else {
                                    $checked1 = FALSE;
                                }

                                $data = array(
                                    'name' => $row->module_id,
                                    'id' => $row->module_id . '_0',
                                    'class' => $row->module_id,
                                    'value' => '0',
                                    'checked' => $checked1,
                                    'style' => 'margin:10px');
                                if($readOnly) {
                                    $data['disabled'] = $readOnly;
                                }
                                echo form_radio($data); ?>
                                NO
                                <div id="dcountry"></div>
                            </td>
                        </tr>
                        <?php
                    }
				}
			}
			?>
			</table>
		</div>
	</section>
    <?php endif; ?>

	<div class="row">
		<div class="col-md-12">
		<?php
			$value = "Add";
			if (isset($edit)) $value = 'Update';
		?>
		<button type="submit" class="btn btn-info pull-right m-top-10 m-right-10"><?php echo $value; ?></button>

		<?php
			//echo form_submit('submit', $value, 'class="btn btn-info pull-right m-top-10" id="sub_btn"');
			echo anchor('user/', 'Cancel', 'class="btn btn-info pull-right m-top-10 m-right-10"');
		?>
		</div>
	</div>

	</section>

</form>

</section>

</section>
</section>


<!-- /End Form -->
<!-- End Data -->


<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/markdown/epiceditor.min.js"></script>

<script src="<?php echo base_url(); ?>assets/js/libs/disableAutoFill/jquery.disableAutoFill.min.js"></script>
<script type="text/javascript">

	var doc_tpl = <?php echo $doc_tpl; ?>;
	$(document).ready(function(){
		$('#user_form').disableAutoFill({
		    passwordField: '#txtpassword',
			debugMode: false,
		    randomizeInputName: false
		});
	});
		
	jQuery(document).ready(function () {
        if (
            $('input[name="RPS_PR"]:checked').length > 0 &&
            $('input[name="RPS_PR"]:checked').val() == 1
        ) {
            const block = $('#TR_AEEPT');
            if (!block.hasClass('is_cloned')) {
                block.insertAfter($('.RPS_PR').parent().parent());
                block.addClass('is_cloned');
                block.css({'display': 'table-row'})
            }
        };

		$('.support').click(function(){
			var val = $(this).prop('checked');
			if(val)
				$('#twilio_level').removeAttr('disabled');
			else
				$('#twilio_level').attr('disabled', 'disabled');
		});


		$('.yearly_hourly_rate').click(function(){
			$('.hourly_rate, .yearly_rate').hide();
			value = $(this).data('value');
			$('.'+value).show();
		});

		/*
		$('.yearly_hourly_rate').click(function(){
			value = $('[name="yearly_hourly_rate"]:checked').val();
			console.log(value);	

		});*/
		
		

		$('#txtyearlyrate').keyup(function(){
			var obj = $(this); 
			var last = <?php echo isset($employee_row->emp_hourly_rate) ? json_encode($employee_row->emp_hourly_rate) : 0 ; ?>;
			var val = $('#txtyearlyrate').val();
			if(val != '' && val)
			{
				rate = val/26/80;
				$('#content').find('#txthourlyrate').val(parseFloat(rate.toFixed(4)));
				return false;
			}
			else{
				//last
				$('#content').find('#txthourlyrate').val(last);
			}
			
			return false;
		});

		/*$('.select_active_status').on('change', function(){
			console.log($(this).val()); 
			$('.employee_settings').slideToggle("slow");
		});*/

		$('.RPS').click(function () {
			if ($('input[name="RPS_GEN"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
			}
			if ($('input[name="RPS_EST"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
			}
			if ($('input[name="RPS_WO"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
				$('#RPS_0').prop('checked', false);

			}
			if ($('input[name="RPS_IN"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
			}
			if ($('input[name="RPS_PR"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
			}
			if ($('input[name="RPS_PRO"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
			}
		});
		$('.RPS_GEN').click(function () {
			if ($('input[name="RPS_GEN"]:checked').length > 0) {
				if ($('input[name="RPS_GEN"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});
		$('.RPS_EST').click(function () {
			if ($('input[name="RPS_EST"]:checked').length > 0) {
				if ($('input[name="RPS_EST"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});
		$('.RPS_WO').click(function () {
			if ($('input[name="RPS_WO"]:checked').length > 0) {
				if ($('input[name="RPS_WO"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});
		$('.RPS_IN').click(function () {
			if ($('input[name="RPS_IN"]:checked').length > 0) {
				if ($('input[name="RPS_IN"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});
		$('.RPS_PR').click(function () {
			if ($('input[name="RPS_PR"]:checked').length > 0) {
                const block = $('#TR_AEEPT');
                if (!block.hasClass('is_cloned')) {
                    block.insertAfter($(this).parent().parent());
                    block.addClass('is_cloned');
                }
				if ($('input[name="RPS_PR"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
                    block.css({'display': 'table-row'})
				} else {
                    $('#AEEPT_0').prop('checked', true);
                    $('#AEEPT_DURING').val(0)
                    block.css({'display': 'none'})
                }
			}
		});
		$('.RPS_PRO').click(function () {
			if ($('input[name="RPS_PRO"]:checked').length > 0) {
				if ($('input[name="RPS_PRO"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});
        $('input[name="GPS"]').on('change', function() {
            if($(this).val() == '1') {
                $(this).parent().find('.switch').removeClass('disabled');
                $(this).parent().find('.switch>#is_tracked').removeAttr('disabled');
            } else {
                $(this).parent().find('.switch').addClass('disabled');
                $(this).parent().find('.switch>#is_tracked').attr('disabled', 'disabled');
                $(this).parent().find('.switch>#is_tracked').prop('checked', false);
            }
        });
        $('input[name="AEEPT"]').on('change', function() {
            if($(this).val() == '1') {
                $(this).parent().find('.switch').removeClass('disabled');
                $(this).parent().find('.switch').removeAttr('disabled');
            } else {
                $(this).parent().find('.switch').addClass('disabled');
                $(this).parent().find('.switch').attr('disabled', 'disabled');
            }
        });
		$('input[name="is_field_estimator"]').click(function () {
			if ($('input[name="is_field_estimator"]:checked').length > 0) {
				if ($('input[name="is_field_estimator"]:checked').val() == 1) {
					$('#is_field_estimator_1').prop('checked', true);
				}
			}
		});
		$('input[name="is_feild_worker"]').click(function () {
			if ($('input[name="is_feild_worker"]:checked').length > 0) {
				if ($('input[name="is_feild_worker"]:checked').val() == 1) {
					$('#is_feild_worker_1').prop('checked', true);
				}
			}
		});
		$('input[name="is_appointment"]').click(function () {
			if ($('input[name="is_appointment"]:checked').length > 0) {
				if ($('input[name="is_appointment"]:checked').val() == 1) {
					$('#is_appointment_1').prop('checked', true);
				}
			}
		});

		 

		$(document).on('click', '.delete-doc', function() {
			if($(this).is('disabled'))
				return false;

			$(this).parents('.certificate-section:first').remove();

			$.each($('.certificate-section .doc_title'), function(key, val) {
				if(($(val).text().indexOf('Document #') + 1))
					$(val).text('Document #' + (key + 1));

			});

			return false;
		});

		$(document).on('click', '.add-doc', function() {
			if($(this).is('disabled'))
				return false;
			
			$('.certificate-section:last').after(doc_tpl.html);
			$('.certificate-section:last input').val('');
            $('.certificate-section:last .delete-doc').show();
			$('.datepicker').datepicker();
			$.each($('.certificate-section .doc_title'), function(key, val) {

				if(($(val).text().indexOf('Document #') + 1))
					$(val).text('Document #' + (key + 1));
				if(key + 1 == $('.certificate-section .doc_title').length) {
					$(val).parents('.certificate-section:first').find('.doc_title').val('Document #' + (key + 1));
				}
			});

			return false;
		});

	});
	$(function () {

		var setMyColorpicker = function (elem) {
			$(elem).colpick({
				submit: 0,
				colorScheme: 'dark',
				onChange: function (hsb, hex, rgb, el, bySetColor) {
					$(el).css('background-color', '#' + hex);
					if (!bySetColor) {
						$(el).val('#' + hex);
						for (var i = 0, len = scopes.data.items.length; i < len; i++) {
							var curItem = scopes.data.items[i];
						}
					}
				}
			}).keyup(function () {
				$(this).colpickSetColor(this.value);
			});
			$('.mycolorpicker').each(function () {
				var current_color = $(this).val();
				var current_color_short = current_color.replace(/^#/, '');
				$(this).colpickSetColor(current_color_short);
			});
		};
		window.setMyColorpicker = setMyColorpicker;
		setTimeout(function () {
			setMyColorpicker($('.mycolorpicker'));
		}, 200);
	});
	
	/*function checkActive(obj)
	{
		console.log($(obj).parent().find('.active_employee').prop('checked', true), $(obj).val());
		if($(obj).val() == 'yes')
			$(obj).parent().find('.active_employee').prop('checked', true);
		else if($(obj).val() == 'no')
			$(obj).parent().find('.active_employee').prop('checked', false);
	}*/
</script>

<link rel="stylesheet" href="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.css'); ?>">
<script src="<?php echo base_url(); ?>assets/js/modules/user/users.js"></script>

<?php if (config_item('default_mail_driver') === 'amazon'): ?>
<script src="<?php echo base_url(); ?>assets/js/modules/mail/mail_check.js?v=0.1"></script>
<?php endif; ?>

<?php $this->load->view('includes/footer'); ?>
