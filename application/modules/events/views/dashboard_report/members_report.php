<?php

if(isset($report) && $report->team && $report->team->schedule_teams_members_user && $report->team->schedule_teams_members_user->count()) : ?>
	<?php foreach($report->team->schedule_teams_members_user as $key => $member) : ?>
        <div class="form-group m-b-none">
			<?php if($key == 0) : ?>
			<label class="control-label">
				<h4>Team Members</h4>
			</label>
			<div class="clear"></div>
			<div class="line line-dashed line-lg"></div>
			<?php endif; ?>
			<?php
            $date = $report->er_report_date_original ? $report->er_report_date_original : $report->er_event_date;

            $expense_count = 0;
            $expense_report = [];
            $worked = [];

            $expense_report = $expense_bld = $expense_extra = false;
            if($date){
                $expense_report = $member->expenses_report->where('ter_date', '=', $date)->first();
                $worked = $member->employeeWorked->firstWhere('worked_date', $date);
                $expense_bld = $member->expense->where('expense_is_extra', '=', 0)->where('expense_date', '=', strtotime($date))->first();
                $expense_extra = $member->expense->where('expense_is_extra', '=', 1)->where('expense_date', '=', strtotime($date))->first();
            }

            $payroll_id = ($worked)?$worked->worked_payroll_id:'';

            $expense_count = ($expense_report)?2:0;
            if($expense_bld)
                $expense_count--;
            if($expense_extra)
                $expense_count--;

            ?>
			<section class="panel panel-default col-sm-12" style="padding: 0">
                <header class="panel-heading bg-light">
					<ul class="nav nav-tabs pull-right">
						<li <?php if(!isset($container)): ?>class="active"<?php endif; ?>><a href="#profile-<?php echo $report->er_id??0; ?>-<?php echo $member->id??''; ?>" data-toggle="tab"><i class="fa fa-clock-o text-default"></i> Times</a></li>
						<li <?php if(isset($container)): ?>class="active"<?php endif; ?>>
							<a href="#settings-<?php echo $report->er_id??0; ?>-<?php echo $member->id??''; ?>" data-toggle="tab">
								<?php if($expense_count): ?>
								<span class="badge bg-warning pull-right">
								<?php echo $expense_count ?>
								</span>
								<?php endif; ?>
								<i class="fa fa-money text-default"></i> Expenses
							</a>
							
						</li>
					</ul>
                  	<span class="hidden-sm">
                  	<a class="chk_payroll" target="_blank" href="<?php echo base_url('employees/payroll/'.$member->id.'/'.$payroll_id); ?>" >
					<?php if($member->id == $report->team->team_leader_id) : ?>
						<strong>
                    <?php endif; ?>
						
						<?php echo $member->full_name; ?>

                    <?php if($member->id == $report->team->team_leader_id) : ?>
						</strong>
					<?php endif; ?>
					</a>
					</span>
                </header>
                <div class="panel-body">
                  <div class="tab-content">

                    <div class="tab-pane <?php if(!isset($container)): ?>active<?php endif; ?>" id="profile-<?php echo $report->er_id??0;; ?>-<?php echo $member->id??''; ?>">
                    	<label class="control-label" style="text-align:left!important;">

							<?php
                            if(isset($worked['logins']) && count($worked['logins'])) : ?>

                                <?php foreach($worked['logins'] as $jkey => $login) : ?>

                                <?php if($jkey == 0) : ?>
                                <div class="m-b-md">No Lunch : <input type="checkbox" name="no_lunch" class="lunch" data-worked-id="<?php echo $worked['worked_id']; ?>" data-date="<?php echo $worked['worked_date']; ?>" <?php if($worked['worked_lunch'] == FALSE) : ?>checked="checked"<?php endif; ?> value="0.5"></div>
                                <?php endif; ?>
                                <div class="log_time_<?php echo $login->login_id; ?>">
                                    <?php if(count($worked['logins']) != 1) : ?>
                                        <br>
                                    <?php endif;?>

                                    <input type="hidden" id="inp-emp-id-<?php echo $login->login_id; ?>" value="<?php echo $login->login_employee_id;?>">
                                    <input type="hidden" id="inp-login-date-<?php echo $login->login_id; ?>" value="<?php echo $login->login_date;?>" class="indate">

                                    Login Time:
                                        <a href="#" class="save_time " name="login" data-id="<?php echo $login->login_id; ?>">
                                            <?php echo date(getPHPTimeFormatWithOutSeconds(), strtotime($login->login));?>
                                        </a><br>
                                    Logout Time:
                                        <a href="#" class="save_time " name="logout" data-id="<?php echo $login->login_id; ?>">
                                            <?php echo $login->logout ? date(getPHPTimeFormatWithOutSeconds(), strtotime($login->logout)) : '-';?>
                                        </a>
                                </div>

                                <?php endforeach; ?>


							<?php endif; ?>
						</label>
			
                    </div>
                    <div class="tab-pane <?php if(isset($container)): ?>active<?php endif; ?>" id="settings-<?php echo $report->er_id??0; ?>-<?php echo $member->id??''; ?>">

                        <form data-type="ajax" id="confirm_members_expenses-<?php echo $expense_report->ter_id??''; ?>" data-url="<?php echo base_url('events/confirm_members_expenses/'.$report->team->team_id.'/'.($member->id??0).'/'.$date); ?>" data-global="false" data-callback="window.update_members_list">
                            <input type="hidden" name="ter_id" value="<?php echo $expense_report->ter_id??''; ?>">
							<input type="hidden" name="team_id" value="<?php echo $report->team->team_id; ?>">
							<input type="hidden" name="user_id" value="<?php echo $member->id??''; ?>">
                            <input type="hidden" name="ter_date" value="<?php echo $expense_report->ter_date??''; ?>">
                        	<div class="row">
	                        	<label class="col-md-2">B.L.D:</label>
	                        	<div class="col-md-7">
                                    <?php if(!$expense_bld): ?>
                                        <?php echo get_currency(); ?>
                                    <a href="#" class="editable editable-report"
									data-name="ter_bld" 
									data-type="text" 
									data-title="B.L.D"
									data-pk="<?php echo $expense_report->ter_id??''; ?>"
									data-url="<?php echo base_url('events/events/update_members_expenses/'.$report->team->team_id.'/'.($member->id??'').'/'.$date); ?>"
									data-mode="inline"
									data-value="<?php echo $expense_report->ter_bld??''; ?>">
										<?php echo $expense_report->ter_bld??''; ?>
									</a>
                                    <?php else: ?>
                                        <?php echo money($expense_bld->expense_amount_with_tax); ?>
                                    <?php endif; ?>
								</div>
								<div class="col-md-3 text-right">
									<?php if($expense_report && $expense_report->ter_id && !$expense_bld): ?>
									<input type="checkbox" name="is_complete_bld" checked="checked">
									<?php else: ?>
										<?php if($expense_report): ?>
                                            Already saved
                                        <?php endif; ?>
									<?php endif; ?>
								</div>
							</div>
							<div class="row">
								<label class="col-md-2">Extra:</label>
								<div class="col-md-7">
                                    <?php
                                    if(!isset($expense_extra) || !$expense_extra): ?>
                                        <?php echo get_currency(); ?>
                                    <a href="#" class="editable editable-report"
									data-name="ter_extra" 
									data-type="text" 
									data-title="Extra"
									data-pk="<?php echo $expense_report->ter_id??''; ?>"
									data-url="<?php echo base_url('events/events/update_members_expenses/'.$report->team->team_id.'/'.($member->id??'').'/'.$date); ?>"
									data-mode="inline"
									data-value="<?php echo $expense_report->ter_extra??0; ?>">
										<?php echo $expense_report->ter_extra??0; ?>
									</a>
                                    <?php else: ?>
                                        <?php echo money($expense_extra->expense_amount_with_tax); ?>
                                    <?php endif; ?>
								</div>
								<div class="col-md-3 text-right">
									<?php if(isset($expense_report->ter_extra) && !isset($expense_extra->expense_amount_with_tax)): ?>
                                    <input type="checkbox" name="is_complete_extra" checked="checked">
									<?php else: ?>
                                        <?php if($expense_report): ?>
										Already saved
                                        <?php endif; ?>
									<?php endif; ?>
								</div>
							</div>
							<?php if(($expense_report->ter_id??0) && $expense_count): ?>
							<div class="row">
								<div class="col-md-12">
									<button class="btn btn-sm btn-success" class="send-expense-report" style="position: absolute;right: 15px;" type="submit">Save to expenses</button>
								</div>
							</div>
							<?php endif; ?>

							<div class="row">
								<label class="col-md-2">Comment:</label>
								<div class="col-md-6">
									<a href="#" class="editable editable-report" 
									data-name="ter_extra_comment" 
									data-type="textarea" 
									data-title="Comment"
									data-pk="<?php echo $expense_report->ter_id??''; ?>"
									data-url="<?php echo base_url('events/events/update_members_expenses/'.$report->team->team_id.'/'.($member->id??'').'/'.$date); ?>"
									data-mode="inline"
									data-value="<?php echo $expense_report->ter_extra_comment??''; ?>">
										<?php echo $expense_report->ter_extra_comment??''; ?>
									</a>
								</div>
								
							</div>
							
						</form>
                    </div>
                  </div>
                </div>
            </section>
			<div class="clear"></div>
			<?php if($key + 1 != $report->team->schedule_teams_members_user->count()) : ?>
				<div class="line line-dashed line-lg"></div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>							
<?php endif; ?>
