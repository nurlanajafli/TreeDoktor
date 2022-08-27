<?php foreach($letters as $key=>$letter) : ?>
<div id="email-<?php echo $letter['email_template_id']; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" style="max-width: 900px;">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Send Email</header>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label">From Estimator</label>
						<div class="controls">
							<select name="fromEstimator" class="input-sm form-control fromEstimator" onchange="changeEmail(<?php echo $letter['email_template_id']; ?>, $('#email-<?php echo $letter['email_template_id']; ?>').find('.fromEstimator option:selected').attr('data-email'));">
								<option value="team_td">From <?php echo $this->config->item('default_email_from_second'); ?></option>
								<option value="">From Estimator</option>
								<option value="0">From Me</option>
								<?php foreach($estimators as $k=>$v) : ?>
									<option  value="<?php echo $v['id'];?>" data-email="<?php echo $v['user_email']; ?>"><?php echo $v['firstname'];?> <?php echo $v['lastname'];?></option>
								<?php endforeach; ?>
							</select>
							
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Email From </label>
						<div class="controls">
							<input class="fromEmail form-control" type="text"
									<?php if(isset($user_email)) : ?>
										value="<?php echo $user_email; ?>"
										<?php else :?>
											value="<?php echo $this->config->item('account_email_address'); ?>"
										<?php endif; ?>
										placeholder="Email from..." style="background-color: #fff;"/>
							
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Email Subject</label>
						<div class="controls">
							<input class="subject form-control" type="text"
								   value="<?php echo $letter['email_template_title']; ?>"
								   placeholder="Email Subject" style="background-color: #fff;"/>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Email Text</label>
						<div class="controls">
							<textarea id="template_text_<?php echo $letter['email_template_id']; ?>" class="form-control" value="">
								<?php echo $letter['email_template_text']; ?>
							</textarea>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" data-save-template="<?php echo $letter['email_template_id']; ?>" >
					<span class="btntext" >Send</span>
					<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
						 class="preloader">
				</button>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
	</div>
</div>
<?php endforeach; ?>
