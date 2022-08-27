<div href="#" class="alert alert-info m-bottom-5 p-5 scheduled-block" <?php if(!isset($scheduled_date) || !isset($scheduled_user_id) || !isset($scheduled_start_time) || !isset($scheduled_end_time) || !$scheduled_date || !$scheduled_user_id || !$scheduled_start_time || !$scheduled_end_time): ?>style="display: none;"<?php endif; ?>>
    <i class="fa fa-times remove-schedule" style="position: absolute; right: 20px; cursor: pointer"></i>
    <div class="scheduled-block-info">
    <i class="fa fa-info-sign"></i><strong class="scheduled-block-username"><?php echo (isset($scheduled_user_name))?$scheduled_user_name: ''; ?></strong>
    <p><label>Date:</label><label class="scheduled-block-date"><?php
        echo (isset($scheduled_date))?date(getDateFormat(), strtotime($scheduled_date)): ''; 
        ?></label>,
        <label>Start:</label><label class="scheduled-block-start"><?php echo (isset($scheduled_start_time))?(new \DateTime($scheduled_start_time))->format(getTimeFormat(true)): ''; ?></label>, <label>End:</label>
        <label class="scheduled-block-end"><?php echo (isset($scheduled_end_time))?(new \DateTime($scheduled_end_time))->format(getTimeFormat(true)): ''; ?></label></p>
    </div>
    <input type="hidden" name="scheduled_user_name" value="<?php echo (isset($scheduled_user_name))?$scheduled_user_name: ''; ?>">
    <input type="hidden" name="scheduled_user_id" value="<?php echo (isset($scheduled_user_id))?$scheduled_user_id: ''; ?>">
    <input type="hidden" name="scheduled_date" value="<?php echo (isset($scheduled_date))?date(getDateFormat(), strtotime($scheduled_date)): ''; ?>">
    <input type="hidden" name="scheduled_start_time" value="<?php echo (isset($scheduled_start_time))?(new \DateTime($scheduled_start_time))->format(getTimeFormat(true)): ''; ?>">
    <input type="hidden" name="scheduled_end_time" value="<?php echo (isset($scheduled_end_time))?(new \DateTime($scheduled_end_time))->format(getTimeFormat(true)): ''; ?>">
    <input type="hidden" name="task_category" value="<?php echo (isset($task_category))?$task_category: 7; ?>">

    <input type="hidden" name="is_client_sms" value="<?php echo (isset($is_client_sms))?$is_client_sms: ''; ?>">
	<input type="hidden" name="is_client_email" value="<?php echo (isset($is_client_email))?$is_client_email: ''; ?>">
	<input type="hidden" name="is_estimator_sms" value="<?php echo (isset($is_estimator_sms))?$is_estimator_sms: ''; ?>">
	<input type="hidden" name="is_estimator_email" value="<?php echo (isset($is_estimator_email))?$is_estimator_email: ''; ?>">
</div>
