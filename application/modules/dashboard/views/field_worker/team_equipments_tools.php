<section class="panel panel-success portlet-item m-bottom-5 b-0">
	<header class="panel-heading h5"><strong><i class="fa fa-fw fa-users"></i>&nbsp;My Team</strong></header>
	<ul class="list-group alt">
		<?php if(!isset($dashboardTeams[$dashboard_date]->items['users']) || empty($dashboardTeams[$dashboard_date]->items['users'])): ?>
		<li class="list-group-item">
			<div class="media">
				<div class="media-body">
					<div class="text-center"><a href="#">Team list is empty</a></div>
				</div>
			</div>
		</li>
		<?php else: ?>
			<?php foreach($dashboardTeams[$dashboard_date]->items['users'] as $user): ?>
			<li class="list-group-item p-top-5 p-bottom-5">
				<div class="media">
					<span class="pull-left thumb-sm"><img src="<?php echo (isset($user->picture) && $user->picture && is_file('assets/' . $this->config->item('company_dir') . '/pictures/' . $user->picture)) ? base_url('assets/' . $this->config->item('company_dir') . '/pictures/' . $user->picture) : base_url('assets/' . $this->config->item('company_dir') . '/pictures/avatar_default.jpg'); ?>" alt="John said" class="img-circle"></span>
					<div class="media-body">
						<div class="p-top-10">
							<a href="#"><?php echo $user['name']; ?></a>
							<?php if($user['item_id']==$user['team_leader_user_id']): ?>
							<label class="label bg-info m-l-xs">Team Leader</label> 
						<?php endif; ?>
						</div>
					</div>
				</div>
			</li>
			<?php endforeach; ?>
		<?php	
		 endif; ?>
	</ul>
</section>


<section class="panel panel-success portlet-item m-bottom-5 b-0">
	<header class="panel-heading h5"><strong><i class="fa fa-fw fa-wrench"></i>&nbsp;Equipment</strong></header>
	<div class="list-group bg-white">
	  	<?php if(!isset($dashboardTeams[$dashboard_date]->items['equipment']) || empty($dashboardTeams[$dashboard_date]->items['equipment'])): ?>
	  	<div class="text-center">
	  		<a href="#" class="list-group-item">Equipment list is empty</a>
	  	</div>
	 	<?php else: ?>
	 		<?php foreach($dashboardTeams[$dashboard_date]->items['equipment'] as $equipment): ?>
	 			<a href="#" class="list-group-item">
			    	<?php /*<i class="fa fa-fw fa-envelope"></i>*/ ?> <?php echo $equipment['name']; ?>
			  	</a>
	 		<?php endforeach; ?>
	 	<?php endif; ?>
	</div>
</section>

<section class="panel panel-success portlet-item m-bottom-5 b-0">
	<header class="panel-heading h5"><strong><i class="fa fa-fw fa-wrench"></i>&nbsp;Tools</strong></header>
	<div class="list-group bg-white">
		<div class="text-center">
			<?php /*
		  	<a href="#" class="list-group-item">
		  		<span class="btn btn-xs btn-success m-t-xs">Follow</span><span href="#" class="btn btn-xs btn-success m-t-xs">Follow</span>
		  	</a>
		  	*/ ?>
	  	</div>
	  	<?php /*if(!count($dashboardTeams[$dashboard_date]->items['equipment'])): ?>
	  	<a href="#" class="list-group-item">
	    	 Tools list is empty
	  	</a>
	 	<?php else: ?>
	 		<?php foreach($dashboardTeams[$dashboard_date]->items['equipment'] as $equipment): ?>
	 			<a href="#" class="list-group-item">
			    	<?php echo $equipment['name']; ?>
			  	</a>
	 		<?php endforeach; ?>
	 	<?php endif; */ ?>
	</div>
</section>
