
<div class="reports-container">
<?php $this->load->view('events/dashboard_report/team_report', ['team'=>$team /*,'command_members'=>$command_members*/]); ?>
</div>
<?php //<div class="members-expenses"> ?>

<?php //$this->load->view('events/dashboard_report/members_report', ['command_members'=>$command_members]); ?>

<?php //</div> ?>
				
<style type="text/css">
	.editable-input textarea{ width: 358px!important; margin-bottom: 5px; }
	.members-expenses .editable-input textarea{ width: 313px!important; margin-bottom: 5px; }
</style>
	<!------End Report-------------->
