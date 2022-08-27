<style>#nav.nav-xs nav.nav-primary>.nav>li>a {padding: 0!important;}</style>
<aside class="bg-light lter b-r aside-md hidden-print hidden-sm<?php if ($this->uri->segment(1) == 'schedule' || $this->uri->segment(1) == 'tree_inventory' /*|| $this->uri->segment(1) == 'stumps'*/ || $this->uri->segment(2) == 'history' || ($this->session->userdata('user_type')=='user' && $this->session->userdata('worker_type') == 1)) : ?> nav-xs<?php endif; ?>"
       id="nav">
<section class="vbox">
<section class="w-f scrollable">
<div class="slim-scroll" data-height="auto" data-disable-fade-out="true" data-distance="0" data-size="5px"
     data-color="#333333">
<nav class="nav-primary hidden-xs">
<ul class="nav">

<li>
	<a href="<?php echo base_url('dashboard'); ?>">
		<i class="fa fa-dashboard icon"><b class="bg-danger"></b></i>
		<span>Dashboard</span>
	</a>
</li>
<?php if ($this->session->userdata('user_type') == "admin" || is_cl_permission_all() || is_cl_permission_owner()) : ?>
	<!---Clients Module- -->
	<li>
		<a href="<?php echo base_url('clients'); ?>">
			<i class="fa fa-users icon"><b class="bg-warning"></b></i>
			<span class="pull-right">
				<i class="fa fa-angle-down text"></i>
				<i class="fa fa-angle-up text-active"></i>
			</span>
			<span>Clients</span>
		</a>
		<ul class="nav lt" style="display: none;">
			<li<?php if ($this->router->fetch_class()  == 'clients' && !$this->uri->segment(2)) : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
				<a href="<?php echo base_url('clients'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Clients List</span>
				</a>
			</li>
			<li<?php if ($this->router->fetch_class() == 'clients' && $this->uri->segment(2) == 'followup') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
				<a href="<?php echo base_url('clients/followup'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Follow Up</span>
				</a>
			</li>
			<li<?php if ($this->uri->segment(1) == 'administration' && $this->uri->segment(2) == 'followup') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
				<a href="<?php echo base_url('administration/followup'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Programmed Messages</span>
				</a>
			</li>
			<?php /*if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('RPS_PRO') == 1) : ?>
				<li<?php if ($this->uri->segment(1) == 'clients' && $this->uri->segment(2) == 'client_communications') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
					<a href="<?php echo base_url('clients/client_communications'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Client Communications</span>
					</a>
				</li>
			<?php endif;*/ ?>
			
			<?php /*
			<li<?php if ($this->uri->segment(1) == 'clients' && $this->uri->segment(2) == 'clients_map') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('clients/clients_map'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Clients Map</span>
				</a>
			</li>
			*/ ?>
			<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('EM_TMP') == 1) : ?>
				<li<?php if ($this->router->fetch_class() == 'clients' && $this->uri->segment(2) == 'letters') : ?> class="active"<?php endif; ?>>
					<a href="<?php echo base_url('clients/letters'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Email Templates</span>
					</a>
				</li>
			<?php endif;?>
			<?php  //if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('EM_TMP') == 1) : ?>
				<li<?php if ($this->router->fetch_class() == 'clients' && ($this->uri->segment(2) == 'client_mailing' || $this->uri->segment(2) == 'emailing_search')) : ?> class="active"<?php endif; ?>>
					<a href="<?php echo base_url('clients/client_mailing'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Newsletters</span>
					</a>
				</li>
				
			<?php //endif; ?>
		
			
			<?php /* if ($this->session->userdata('user_type') == "admin") : ?>
				<li<?php if ($this->router->fetch_class() == 'clients' && $this->uri->segment(2) == 'voices') : ?> class="active"<?php endif; ?>>
					<a href="<?php echo base_url('clients/voices'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Voice Templates</span>
					</a>
				</li>
			<?php endif; */?>
			<?php if (config_item('messenger') && ($this->session->userdata('user_type') == "admin" || is_cl_permission_all() || is_cl_permission_owner())) : ?>
				<li<?php if ($this->uri->segment(1) == 'clients' && $this->uri->segment(2) == 'sms') : ?> class="active"<?php endif; ?>>
					<a href="<?php echo base_url('clients/sms'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>SMS Templates</span>
					</a>
				</li>
			<?php endif;?>
			<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('SCR') == 1) : ?>
				<li<?php if ($this->router->fetch_class() == 'clients' && $this->uri->segment(2) == 'scripts') : ?> class="active"<?php endif; ?>>
					<a href="<?php echo base_url('clients/scripts'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Scripts</span>
					</a>
				</li>
			<?php endif; ?>
		</ul>
	</li>
	<!---END Clients Module- -->
<?php endif; ?>
<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('STP')) : ?>
<li>
	<a href="<?php echo base_url('stumps'); ?>">
		<i class="fa fa-dot-circle-o icon"><b class="bg-info"></b></i>
		<span class="pull-right">
			<i class="fa fa-angle-down text"></i>
			<i class="fa fa-angle-up text-active"></i>
		</span>
		<span>Contracts</span>
	</a>
	
		<ul class="nav lt" style="display: none;">
			
		<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('STP') == 1) : ?>
			<li<?php if ($this->uri->segment(1) == 'stumps' && $this->uri->segment(2) == 'stumps_clients') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('stumps/stumps_clients'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Clients</span>
				</a>
			</li>
			<!---END Stumps Module- -->
		<?php endif; ?>
		<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('STP') == 1 || $this->session->userdata('STP') == 3) : ?>
			<li<?php if ($this->uri->segment(1) == 'stumps' && $this->uri->segment(2) == 'stumps_list') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('stumps/stumps_list'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Lists</span>
				</a>
			</li>
			<!---END Stumps Module -->
		<?php endif; ?>
		<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('STP') == 1 || $this->session->userdata('STP') == 3) : ?>
			<li<?php if ($this->uri->segment(1) == 'stumps' && $this->uri->segment(2) == 'stumps_mapper') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('stumps/stumps_mapper'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Map</span>
				</a>
			</li>
			<!---END Stumps Module -->
		<?php endif; ?>
		<?php if ($this->session->userdata('user_type') == "admin" || ($this->session->userdata('STP') && $this->session->userdata('STP') != 3)) : ?>
			<li<?php if ($this->uri->segment(1) == 'stumps' && $this->uri->segment(2) == 'my_stumps') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('stumps/my_stumps'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>My Lists</span>
				</a>
			</li>
			<!---END Stumps Module -->
		<?php endif; ?>
		<?php if ($this->session->userdata('user_type') == "admin" || ($this->session->userdata('STP') && $this->session->userdata('STP') != 3)) : ?>
			<li<?php if ($this->uri->segment(1) == 'stumps' && $this->uri->segment(2) == 'my_mapper') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('stumps/my_mapper'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>My Map</span>
				</a>
			</li>
			<!---END Stumps Module- -->
		<?php endif; ?>
		<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('STP') == 1 || $this->session->userdata('STP') == 3) : ?>
			<li<?php if ($this->uri->segment(1) == 'stumps' && $this->uri->segment(2) == 'report') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('stumps/report'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Contracts Report</span>
				</a>
			</li>
			<!---END Stumps Module - -->
		<?php endif; ?>
		</ul>
</li>
<?php /*<li>
    <a href="<?php echo base_url('jobs/manage'); ?>">
        <i class="fa fa-gears icon"><b class="bg-success"></b></i>
        <span>Jobs</span>
    </a>
</li>*/ ?>
<?php endif; ?>
<?php if ($this->session->userdata('user_type') == "admin" || is_cl_permission_all() || is_cl_permission_owner()) : ?>
	<!---Leads Module- -->
	<li>
		<a href="<?php echo base_url('leads'); ?>">
			<i class="fa fa-bars icon"><b class="bg-success"></b></i>
									<span class="pull-right">
										<i class="fa fa-angle-down text"></i>
										<i class="fa fa-angle-up text-active"></i>
									</span>
            <b class="badge bg-danger pull-right m-r-sm" id="root_not_approved_leads" style="display: none;"></b>
			<span>Leads</span>
		</a>
		<ul class="nav lt" style="display: none;">
			<li<?php if ($this->uri->segment(1) == 'leads' && !$this->uri->segment(2)) : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
				<a href="<?php echo base_url('leads'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Leads List</span>
				</a>
			</li>
			<li<?php if ($this->uri->segment(1) == 'leads' && $this->uri->segment(2) == 'map') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('leads/map'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Leads Map</span>
				</a>
			</li>
			<?php /*
			<li<?php if ($this->uri->segment(1) == 'leads' && $this->uri->segment(2) == 'leads_statuses') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('leads/leads_statuses'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Leads Categories</span>
				</a>
			</li>
			*/ ?>
			<li<?php if ($this->uri->segment(1) == 'leads' && $this->uri->segment(2) == 'for_approval') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('leads/for_approval'); ?>">
					<b class="badge bg-danger pull-right" id="not_approved_leads" style="display: none;"></b>
					<i class="fa fa-angle-right"></i>
					<span>For Approval</span>
				</a>
			</li>
            <?php if(is_admin()) : ?>
			<li<?php if ($this->uri->segment(1) == 'leads' && $this->uri->segment(2) == 'lead_statuses') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('leads/lead_statuses'); ?>">
					<b class="badge bg-danger pull-right" style="display: none;"></b>
					<i class="fa fa-angle-right"></i>
					<span>Lead Statuses</span>
				</a>
			</li>
            <?php endif; ?>
            <li<?php if ($this->uri->segment(1) == 'leads' && $this->uri->segment(2) == 'tree_inventory' && $this->uri->segment(3) == 'tree_types') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('leads/tree_inventory/tree_types'); ?>">
					<b class="badge bg-danger pull-right" style="display: none;"></b>
					<i class="fa fa-angle-right"></i>
					<span>Tree Types</span>
				</a>
			</li>
            <li<?php if ($this->uri->segment(1) == 'leads' && $this->uri->segment(2) == 'Work_types.php') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('leads/tree_inventory/work_types'); ?>">
					<b class="badge bg-danger pull-right" style="display: none;"></b>
					<i class="fa fa-angle-right"></i>
					<span>Work Types</span>
				</a>
			</li>
		</ul>
	</li>
	<!---END Leads Module- -->
<?php endif; ?>

<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('TSKS') == TRUE) : ?>
	<!---Tasks Module- -->
	<li>
		<a href="<?php echo base_url('tasks'); ?>">
			<i class="fa fa-tasks icon"><b class="bg-danger"></b></i>
			<span class="pull-right">
				<i class="fa fa-angle-down text"></i>
				<i class="fa fa-angle-up text-active"></i>
			</span>
			<span>Tasks</span>
		</a>
		<ul class="nav lt" style="display: none;">
			<li<?php if ($this->uri->segment(1) == 'tasks' && !$this->uri->segment(2)) : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
				<a href="<?php echo base_url('tasks'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Tasks</span>
				</a>
			</li>
			<li<?php if ($this->uri->segment(1) == 'tasks' && $this->uri->segment(2) == 'tasks_mapper') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('leads/map'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Tasks Map</span>
				</a>
			</li>
			<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('TM') == 1) : ?>
				<li<?php if ($this->uri->segment(1) == 'tasks' && $this->uri->segment(2) == 'tasks_categories') : ?> class="active"<?php endif; ?>>
					<a href="<?php echo base_url('tasks/tasks_categories'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Tasks Categories</span>
					</a>
				</li>
			<?php endif; ?>
		</ul>
	</li>
	<!---END Tasks Module- -->
<?php endif; ?>
<?php if ($this->session->userdata('user_type') == "admin" || is_cl_permission_all() || is_cl_permission_owner()) : ?>
	<!---Estimates Module- -->
	<li>
		<a href="<?php echo base_url('estimates'); ?>">
			<i class="fa fa-columns icon"><b class="bg-warning"></b></i>
			<span class="pull-right">
				<i class="fa fa-angle-down text"></i>
				<i class="fa fa-angle-up text-active"></i>
			</span>
			<span>Estimates</span>
		</a>
		<?php $estimatesUrl = ($this->router->fetch_class() == 'estimates' && !$this->uri->segment(2)) ? '' : base_url('estimates'); ?>
		<ul class="nav lt" data-type="estimates" data-action="stop" style="display: none;">
			<li<?php if ($this->router->fetch_class() == 'estimates' && $this->uri->segment(2) == 'own') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('estimates/own'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Own</span>
				</a>
			</li>
			<li<?php if ($this->router->fetch_class() == 'estimates' && !$this->uri->segment(2)) : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
				<a href="<?php echo base_url('estimates'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>List</span>
				</a>
			</li>
            <?php /*
            <li<?php if ($this->router->fetch_class() == 'estimates' && $this->uri->segment(2) == 'estimates_mapper') : ?> class="active"<?php endif; ?>>
				<a href=<?php echo base_url('estimates/estimates_mapper'); ?>>
					<i class="fa fa-angle-right"></i>
					<span>Map</span>
				</a>
			</li>

			<li<?php if ($this->router->fetch_class() == 'estimates' && $this->uri->segment(2) == 'estimates_by_areas') : ?> class="active"<?php endif; ?>>
				<a href=<?php echo base_url('estimates/estimates_by_areas'); ?>>
					<i class="fa fa-angle-right"></i>
					<span>Estimates By Areas</span>
				</a>
			</li>
			*/ ?>
			<?php if ($this->session->userdata('user_type') == "admin") : ?>
				<li<?php if ($this->router->fetch_class() == 'estimates' && $this->uri->segment(2) == 'estimate_status') : ?> class="active"<?php endif; ?>>
					<a href="<?php echo base_url('estimates/estimate_status'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Statuses</span>
					</a>
				</li>
			<?php endif; ?>

			<?php if ($this->session->userdata('user_type') == "admin" || is_cl_permission_owner() || is_cl_permission_all()) : ?>
				<li<?php if ($this->router->fetch_class() == 'estimates' && $this->uri->segment(2) == 'services') : ?> class="active"<?php endif; ?>>
					<a href="<?php echo base_url('estimates/services'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Services</span>
					</a>
				</li>
				<li<?php if ($this->router->fetch_class() == 'estimates' && $this->uri->segment(2) == 'services') : ?> class="active"<?php endif; ?>>
					<a href="<?php echo base_url('estimates/products'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Products</span>
					</a>
				</li>
                <li<?php if ($this->router->fetch_class() == 'estimates' && $this->uri->segment(2) == 'services') : ?> class="active"<?php endif; ?>>
                    <a href="<?php echo base_url('estimates/bundles'); ?>">
                        <i class="fa fa-angle-right"></i>
                        <span>Bundles</span>
                    </a>
                </li>

			<?php endif; ?>

			
			<?php if ($this->session->userdata('user_type') == "admin") : ?>
				<li<?php if ($this->router->fetch_class() == 'estimates' && $this->uri->segment(2) == 'new_equipments') : ?> class="active"<?php endif; ?>>
					<a href="<?php echo base_url('estimates/estimate_equipment'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Equipment Types</span>
					</a>
				</li>
				<?php /*<li<?php if ($this->router->fetch_class() == 'estimates' && $this->uri->segment(2) == 'estimate_equipment') : ?> class="active"<?php endif; ?>>
					<a href="<?php echo base_url('estimates/estimate_equipment'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Estimates Equipment</span>
					</a>
				</li> */ ?>
				<li<?php if ($this->router->fetch_class() == 'estimates' && $this->uri->segment(2) == 'declines') : ?> class="active"<?php endif; ?>>
					<a href="<?php echo base_url('estimates/declines'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Decline Reasons</span>
					</a>
				</li>
			<?php endif; ?>
            <?php if(isSystemUser()) : ?>
                <li<?php if ($this->router->fetch_class() == 'estimates' && $this->uri->segment(2) == 'service_status') : ?> class="active"<?php endif; ?>>
                    <a href="<?php echo base_url('estimates/service_status'); ?>">
                        <i class="fa fa-angle-right"></i>
                        <span>Service Statuses <span class="badge badge-warning">root</span></span>
                    </a>
                </li>
                <li<?php if ($this->router->fetch_class() == 'estimates' && $this->uri->segment(2) == 'scheme_items') : ?> class="active"<?php endif; ?>>
                    <a href="<?php echo base_url('estimates/scheme_items'); ?>">
                        <i class="fa fa-angle-right"></i>
                        <span>Scheme Items <span class="badge badge-warning">root</span></span>
                    </a>
                </li>
            <?php endif; ?>
		</ul>
	</li>
	<!---END Estimates Module- -->

	<!---Tree Inventory- -->
	<?php /*
	<li>
		<a href="<?php echo base_url('tree_inventory'); ?>">
			<i class="fa fa-gavel icon"><b class="bg-success"></b></i>
			<span class="pull-right">
				<i class="fa fa-angle-down text"></i>
				<i class="fa fa-angle-up text-active"></i>
			</span>
			<span>Tree Inventory</span>
		</a>
		<?php $estimatesUrl = ($this->router->fetch_class() == 'tree_inventory' && !$this->uri->segment(2)) ? '' : base_url('tree_inventory'); ?>
		<ul class="nav lt" data-type="tree_inventory" data-action="stop" style="display: none;">
			<li<?php if ($this->router->fetch_class() == 'tree_inventory' && $this->uri->segment(2) == 'map') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('tree_inventory/map'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Map</span>
				</a>
			</li>
			
			<li<?php if ($this->router->fetch_class() == 'tree_inventory' && $this->uri->segment(2) == 'list') : ?> class="active"<?php endif; ?>>
				<a href=<?php echo base_url('estimates/estimates_mapper'); ?>>
					<i class="fa fa-angle-right"></i>
					<span>Tree Inventory</span>
				</a>
			</li>
		</ul>
	</li>
	*/ ?>
	<!---Tree Inventory- -->


<?php endif; ?>

<?php if ($this->session->userdata('user_type') == "admin" || is_cl_permission_all() || is_cl_permission_owner()) : ?>
	<!---Workorders Module- -->
	<li>
		<a href="<?php echo base_url('workorders'); ?>">
			<i class="fa fa-gears icon"><b class="bg-info"></b></i>
			<span class="pull-right">
				<i class="fa fa-angle-down text"></i>
				<i class="fa fa-angle-up text-active"></i>
			</span>

			<span>Workorders</span>
		</a>
		<ul class="nav lt" data-type="workorders" data-action="stop" style="display: none;">
			<li<?php if ($this->router->fetch_class() == 'workorders' && !$this->uri->segment(2)) : ?> class="active"<?php else: ?> class="inactive"<?php endif; ?>>
				<a href="<?php echo base_url('workorders'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Workorders</span>
				</a>
			</li>
			<li<?php if ($this->router->fetch_class() == 'workorders' && $this->uri->segment(2) == 'map') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('workorders/workorders_mapper'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Workorders Map</span>
				</a>
			</li>
			<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('WO_STS') == 1) : ?>
				<li<?php if ($this->router->fetch_class() == 'workorders' && $this->uri->segment(2) == 'status') : ?> class="active"<?php endif; ?>>
					<a href="<?php echo base_url('workorders/status'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Workorders Statuses</span>
					</a>
				</li>
			<?php endif; ?>
		</ul>
	</li>
	<!---END Workorders Module- -->
<?php endif; ?>

<?php if($this->session->userdata('user_type') == "admin" || $this->session->userdata('SCHD') == 1) : ?>
	<!---Schedule Module- -->
	<li<?php if ($this->uri->segment(1) == 'schedule' && !$this->uri->segment(2)) : ?> class="active"<?php endif; ?>>
		<a href="<?php echo base_url('schedule'); ?>">
			<i class="fa fa-calendar icon"><b class="bg-success"></b></i>
									<span class="pull-right">
										<i class="fa fa-angle-down text"></i>
										<i class="fa fa-angle-up text-active"></i>
									</span>
			<span>Schedule</span>
		</a>
		<ul class="nav lt" style="display: none;">
			<li<?php if ($this->uri->segment(1) == 'schedule' && !$this->uri->segment(2)) : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
				<a href="<?php echo base_url('schedule'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Crew Schedule</span>
				</a>
			</li>
			<?php /*
			<li<?php if ($this->uri->segment(1) == 'schedule' && $this->uri->segment(2) == 'map') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('schedule/map'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Crew Schedule Map</span>
				</a>
			</li>
			<li<?php if ($this->uri->segment(1) == 'schedule' && $this->uri->segment(2) == 'current_jobs') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('schedule/current_jobs'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Current Jobs</span>
				</a>
			</li>
			*/ ?>
			<li<?php if ($this->uri->segment(1) == 'schedule' && $this->uri->segment(2) == 'office') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('schedule/office'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Office Schedule</span>
				</a>
			</li>
			<!--<li<?php if ($this->uri->segment(1) == 'schedule' && $this->uri->segment(2) == 'office_map') : ?> class="active"<?php endif; ?>>
				<a href="#<?php //echo base_url('schedule/office_map'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Office Schedule Map</span>
				</a>
			</li> -->
			<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('ST_OBJ') == 1) : ?>
				<li<?php if ($this->uri->segment(1) == 'schedule' && $this->uri->segment(2) == 'objects') : ?> class="active"<?php endif; ?>>
					<a href="<?php echo base_url('schedule/objects'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Static Objects On Map</span>
					</a>
				</li>
			<?php endif; ?>
			<li<?php if ($this->uri->segment(1) == 'schedule' && $this->uri->segment(2) == 'open_team') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('schedule/open_team'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Not Reviewed Days</span>
				</a>
			</li>
			<li<?php if ($this->session->userdata('user_type') == "admin" && $this->uri->segment(1) == 'schedule' && $this->uri->segment(2) == 'price_difference') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('schedule/price_difference'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Schedule Financial Audit</span>
				</a>
			</li>
		</ul>
	</li>
	<!---END Schedule Module- -->
<?php endif; ?>


<?php if ($this->session->userdata('user_type') == "admin" || is_cl_permission_all() || is_cl_permission_owner()) : ?>
	<!---Invoices Module- -->
	<li>
		<a href="<?php echo base_url('invoices'); ?>">
			<i class="fa fa-money icon"><b class="bg-danger"></b></i>
									<span class="pull-right">
										<i class="fa fa-angle-down text"></i>
										<i class="fa fa-angle-up text-active"></i>
									</span>
			<span>Invoices</span>
		</a>
		<?php $invoicesUrl = ($this->router->fetch_class() == 'invoices' && !$this->uri->segment(2)) ? '' : ''; ?>
		<ul class="nav lt" data-type="invoices" data-action="stop" style="display: none;">
			<li>
				<a href="<?php echo base_url('invoices'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Invoices</span>
				</a>
			</li>
            <?php
            /*
            <li<?php if ($this->router->fetch_class() == 'invoices' && $this->uri->segment(2) == 'invoices_mapper') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('invoices/invoices_mapper'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Invoices Map</span>
				</a>
			</li>
            */
            ?>
			<li<?php if ($this->router->fetch_class() == 'invoices' && $this->uri->segment(2) == 'invoice_status') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('invoices/invoice_status'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Invoice Statuses</span>
				</a>
			</li>
			<?php if ($this->session->userdata('user_type') == "admin") : ?>
				<li<?php if ($this->uri->segment(1) == 'reports' && $this->uri->segment(2) == 'invoices_overdue') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
					<a href="<?php echo base_url('invoices/invoices_overdue'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Late Payment Fees</span>
					</a>
				</li>
			<?php endif; ?>
			<?php /*
			<li<?php if ($this->uri->segment(1) == 'invoices' && $this->uri->segment(2) == 'statuses') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('invoices/statuses'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Invoices Statuses</span>
				</a>
			</li>
			*/ ?>
		</ul>
	</li>
	<!---END Invoices Module- -->
<?php endif; ?>
<?php /*if($this->session->userdata('RPS_IN') == 1 || $this->session->userdata('user_type') == "admin") : */?><!--
	<li>
		<a href="<?php /*echo base_url('qa'); */?>">
			<i class="fa fa-thumbs-up icon"><b class="bg-warning"></b></i>
			<span class="pull-right">
				<i class="fa fa-angle-down text"></i>
				<i class="fa fa-angle-up text-active"></i>
			</span>
			<span>Quality Assurance</span>
		</a>
		<ul class="nav lt" style="display: none;">
			<li<?php /*if ($this->uri->segment(1) == 'qa' && !$this->uri->segment(2)) : */?> class="active"<?php /*else : */?> class="inactive"<?php /*endif; */?>>
				<a href="<?php /*echo base_url('qa'); */?>">
					<i class="fa fa-angle-right"></i>
					<span>QA</span>
				</a>
			</li>-->
			<?php /*<li<?php if ($this->uri->segment(1) == 'qa' && $this->uri->segment(2) == 'invoices') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('qa/invoices'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>QA Invoices</span>
				</a>
			</li>*/ ?>
		<!--</ul>
	</li>
--><?php /*endif; */?>
<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('EQMTS') == 1 || $this->session->userdata('EQMTS') == 2) : ?>
	<!---Equipments Module- -->
	<li>
        <a href="<?php echo base_url('equipment'); ?>">
			<i class="fa fa-truck icon"><b class="bg-info"></b></i>
			<span class="pull-right">
				<i class="fa fa-angle-down text"></i>
				<i class="fa fa-angle-up text-active"></i>
			</span>
            <b class="badge bg-danger pull-right m-r-sm" id="equipment_root_counter" style="display: none;"></b>
			<span>Equipment</span>
		</a>
		<ul class="nav lt" style="display: none;">
            <li<?php if ($this->uri->segment(1) == 'equipment' && (!$this->uri->segment(2) || $this->uri->segment(2) == 'group')) : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
                <a href="<?php echo base_url('equipment'); ?>">
					<i class="fa fa-angle-right"></i>
                    <span>Equipment</span>
				</a>
			</li>
            <li<?php if ($this->uri->segment(1) == 'equipment' && $this->uri->segment(2) == 'groups') : ?> class="active"<?php endif; ?>>
                <a href="<?php echo base_url('equipment/groups'); ?>">
                    <i class="fa fa-angle-right"></i>
                    <span>Groups</span>
                </a>
            </li>
            <?php if(config_item('gps_enabled')) : ?>
                <li<?php if ($this->uri->segment(1) == 'equipment' && $this->uri->segment(2) == 'map') : ?> class="active"<?php endif; ?>>
                    <a href="<?php echo base_url('equipment/map'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Equipment Map</span>
				</a>
			</li>
            <?php endif; ?>
            <li<?php if ($this->uri->segment(1) == 'equipment' && $this->uri->segment(2) == 'services') : ?> class="active"<?php endif; ?>>
                <a href="<?php echo base_url('equipment/services'); ?>">
					<i class="fa fa-angle-right"></i>
                    <b class="badge bg-danger pull-right m-r-sm" id="equipment_due_services" style="display: none;"></b>
                    <span>Services</span>
				</a>
			</li>
            <li<?php if ($this->uri->segment(1) == 'equipment' && $this->uri->segment(2) == 'service-reports') : ?> class="active"<?php endif; ?>>
                <a href="<?php echo base_url('equipment/service-reports'); ?>">
                    <i class="fa fa-angle-right"></i>
                    <span>Service Reports</span>
                </a>
            </li>
            <li<?php if ($this->uri->segment(1) == 'equipment' && $this->uri->segment(2) == 'repairs' && $this->uri->segment(3) != 'my') : ?> class="active"<?php endif; ?>>
                <a href="<?php echo base_url('equipment/repairs'); ?>">
					<i class="fa fa-angle-right"></i>
                    <b class="badge bg-danger pull-right m-r-sm" id="equipment_issued_repair_requests" style="display: none;"></b>
					<span>Repair Requests</span>
				</a>
			</li>
            <li<?php if ($this->uri->segment(1) == 'equipment' && $this->uri->segment(2) == 'settings') : ?> class="active"<?php endif; ?>>
                <a href="<?php echo base_url('equipment/settings'); ?>">
                    <i class="fa fa-angle-right"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li<?php if ($this->uri->segment(1) == 'equipment' && $this->uri->segment(2) == 'sold') : ?> class="active"<?php endif; ?>>
                <a href="<?php echo base_url('equipment/sold'); ?>">
                    <i class="fa fa-angle-right"></i>
                    <span>Sold Equipment</span>
                </a>
            </li>
            <!--            <li<?php if ($this->uri->segment(1) == 'equipment' && $this->uri->segment(2) == 'repairs' && $this->uri->segment(3) == 'my') : ?> class="active"<?php endif; ?> >
				<a href="<?php echo base_url('equipment/repairs/my'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>My Repair Requests</span>
				</a>
			</li>
-->
			<!--Equipment Services- -->
            <!--			<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('EQ_SRV') == 1) : ?>
				<li <?php if ($this->uri->segment(1) == 'equipment' && $this->uri->segment(2) == 'service_types') : ?> class="active"<?php endif; ?> >
					<a href="<?php echo base_url('equipment/service_types'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Eq Services Types</span>
					</a>
				</li>
			<?php endif; ?>
-->
			
			<!--Equipment Services End- -->
			
		</ul>
	</li>
	
	<!---END Reports Module- -->
<?php endif; ?>

<?php if(($this->session->userdata('UHR') == 1 || $this->session->userdata('EMP_ED') == 1 || $this->session->userdata('user_type') == "admin" || $this->session->userdata('RPS_PR') == 1 || $this->session->userdata('RPS_PRO') == 1) && $this->session->userdata('STP') != "3") : ?>
	<!---Equipments Module- -->
	<li<?php if($this->uri->segment(1) == 'employees') : ?> class="active"<?php endif; ?>>
		<a href="<?php echo base_url('employees/crews'); ?>">
			<i class="fa fa-male icon"><b class="bg-success"></b></i>
			<span class="pull-right">
				<i class="fa fa-angle-down text"></i>
				<i class="fa fa-angle-up text-active"></i>
			</span>
			<span>Personnel</span>
		</a>
		<ul class="nav lt"<?php if($this->uri->segment(1) != 'employees') : ?> style="display: none;"<?php endif; ?>>
			<?php if($this->session->userdata('EMP_ED') == 1 || $this->session->userdata('user_type') == "admin") : ?>
			<li<?php if ($this->uri->segment(1) == 'employees' && ($this->uri->segment(2) == 'bonuses' || $this->uri->segment(2) == 'crews' || $this->uri->segment(2) == 'reasons')) : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
				<a href="<?php echo base_url('employees/rules'); ?>">                            
					<i class="fa fa-angle-down text"></i>
					<i class="fa fa-angle-up text-active"></i>
					<span>HR</span>
				</a>
				<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('CRW') == 1 || $this->session->userdata('RS_ABS') == 1) : ?>
				<ul class="nav bg">
					<?php /* if ($this->session->userdata('user_type') == "admin") : ?>
					<li<?php if ($this->uri->segment(1) == 'employees' && $this->uri->segment(2) == 'bonuses') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
						<a href="<?php echo base_url('employees/bonuses'); ?>">
							<i class="fa fa-angle-right"></i>
							<span>Bonus Types</span>
						</a>
					</li>
					<?php endif; */ ?>
					<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('CRW') == 1) : ?>
					<li<?php if ($this->uri->segment(1) == 'employees' && $this->uri->segment(2) == 'crews') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
						<a href="<?php echo base_url('employees/crews'); ?>">
							<i class="fa fa-angle-right"></i>
							<span>Employee Roles</span>
						</a>
					</li>
					<?php endif; ?>
					
					
					<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('RS_ABS') == 1) : ?>
					<li<?php if ($this->uri->segment(1) == 'employees' && $this->uri->segment(2) == 'reasons') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
						<a href="<?php echo base_url('employees/reasons'); ?>">
							<i class="fa fa-angle-right"></i>
							<span>Reasons Of Absence</span>
						</a>
					</li>
					<?php endif; ?>
				</ul>
				<?php endif; ?>
			</li>
			<?php endif; ?>
			
			<!------------------------------------- PAYROLL------------------------------------------------------- -->
			<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('RPS_PR') == 1 || $this->session->userdata('RPS_PRO') == 1) : ?>
			<li<?php if ($this->uri->segment(1) == 'employees' && ($this->uri->segment(2) == 'payroll' || $this->uri->segment(2) == 'payroll_overview')) : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
				<a href="<?php echo base_url('employees/payroll'); ?>">                            
					<i class="fa fa-angle-down text"></i>
					<i class="fa fa-angle-up text-active"></i>
					<span>Payroll</span>
				</a>
				<ul class="nav bg">
					<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('RPS_PR') == 1) : ?>
					<li<?php if ($this->uri->segment(1) == 'employees' && $this->uri->segment(2) == 'payroll') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
						<a href="<?php echo base_url('employees/payroll'); ?>">
							<i class="fa fa-angle-right"></i>
							<span>Payroll Calculations</span>
						</a>
					</li>
					<?php endif; ?>
					<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('RPS_PRO') == 1) : ?>
					<li<?php if ($this->uri->segment(1) == 'employees' && $this->uri->segment(2) == 'payroll_overview') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
						<a href="<?php echo base_url('employees/payroll_overview'); ?>">
							<i class="fa fa-angle-right"></i>
							<span>Payroll Overview</span>
						</a>
					</li>
					<?php endif; ?>					
				</ul>
			</li>
			<?php endif; ?>
			<!-------------------------------------END PAYROLL------------------------------------------------------- -->
			
			
		</ul>
		
	</li>
<?php endif; ?>

<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('RPS_GEN') == 1 || $this->session->userdata('EXP') == 1) : ?>
	<!---Reports Module- -->
	<li class="<?= $this->uri->segment(2) == 'sales_targets' || $this->uri->segment(2) == 'expense_types' || $this->uri->segment(2) == 'expenses' ? 'active' : 'inactive'?>">
		<a href="#">
			<i class="fa fa-bar-chart-o icon"><b class="bg-danger"></b></i>
									<span class="pull-right">
										<i class="fa fa-angle-down text"></i>
										<i class="fa fa-angle-up text-active"></i>
									</span>
			<span>Accounting</span>
		</a>
		<ul class="nav lt">
			<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('EXP') == 1) : ?>
				<li<?php if ($this->uri->segment(1) == 'reports' && $this->uri->segment(2) == 'expenses') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
					<a href="<?php echo base_url('reports/expenses'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Expenses</span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ($this->session->userdata('user_type') == "admin") : ?>
				<li<?php if ($this->uri->segment(1) == 'reports' && $this->uri->segment(2) == 'expense_types') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
					<a href="<?php echo base_url('reports/expense_types'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Expenses Types</span>
					</a>
				</li>
			<?php endif; ?>
			
			<?php if ($this->session->userdata('user_type') == "admin") : ?>
				<li<?php if ($this->uri->segment(1) == 'reports' && $this->uri->segment(2) == 'sales_targets') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
					<a href="<?php echo base_url('reports/sales_targets'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Sales Targets</span>
					</a>
				</li>
			<?php endif; ?>
			<?php /*if ($this->session->userdata('user_type') == "admin") : ?>
				<li<?php if ($this->uri->segment(1) == 'reports' && $this->uri->segment(2) == 'performance') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
					<a href="<?php echo base_url('reports/performance'); ?>">
						<i class="fa fa-angle-right"></i>
						<span>Performance</span>
					</a>
				</li>
			<?php endif;*/ ?>
			<?php /*if ($this->session->userdata('user_type') == "admin") { ?>
				<li<?php if ($this->uri->segment(2) == 'payroll') : ?> class="active"<?php endif; ?>><?php echo anchor('reports/payroll', '<i class="fa fa-angle-right"></i>Old Payroll', ''); ?></li>
			<?php } ?>
			<?php if ($this->session->userdata('user_type') == "admin") { ?>
				<li<?php if ($this->uri->segment(2) == 'payroll_overview') : ?> class="active"<?php endif; ?>><?php echo anchor('reports/payroll_overview', '<i class="fa fa-angle-right"></i>Old Payroll Overview', ''); ?></li>
			<?php }*/ ?>
			
		</ul>
	</li>
	<!---END Reports Module- -->
<?php endif; ?> 
<?php if ($this->session->userdata('user_type') == "admin" || is_cl_permission_all() || is_cl_permission_owner()) : ?>
	<!---Reports Module- -->
	<li style="min-height: 44px;"  class="<?= $this->uri->segment(1) == 'business_intelligence' ? 'active' : 'inactive'?>" >
		<a style="min-height: 44px; padding: 12px 12px 12px 15px;" href="#">
			<i class="fa fa-money icon"><b class="bg-warning"></b></i>
									<span class="pull-right">
										<i class="fa fa-angle-down text"></i>
										<i class="fa fa-angle-up text-active"></i>
									</span>
									
			<span>Business Intelligence</span>
		</a>
		<ul class="nav lt">
			<?php if ($this->session->userdata('user_type') == "admin" || is_cl_permission_all() || is_cl_permission_owner()) : ?>
				<li<?php if ($this->uri->segment(1) == 'business_intelligence' && ($this->uri->segment(2) == 'emails_stat' || $this->uri->segment(2) == 'statistic') || $this->uri->segment(2) == 'refferals_users' || $this->uri->segment(2) == 'refferals_clients' || $this->uri->segment(2) == 'lead_statistics') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
					<a href="<?php echo base_url('business_intelligence/lead_statistics'); ?>">                            
						<i class="fa fa-angle-down text"></i>
						<i class="fa fa-angle-up text-active"></i>
						<span>Clients</span>
					</a>
					<ul class="nav bg">
						<?php if ($this->session->userdata('user_type') == "admin" || is_cl_permission_all() || is_cl_permission_owner()) : ?>
							<li<?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'lead_statistics') : ?> class="active"<?php endif; ?>>
								<a href="<?php echo base_url('business_intelligence/lead_statistics'); ?>">
									<b class="badge bg-danger pull-right" id="not_approved_leads" style="display: none;"></b>
									<i class="fa fa-angle-right"></i>
									<span>Lead Statistics</span>
								</a>
							</li>
						<?php endif; ?>
						<?php if ($this->session->userdata('user_type') == "admin" || is_cl_permission_all() || is_cl_permission_owner()) : ?>
							<li<?php if ($this->router->fetch_class() == 'business_intelligence' && $this->uri->segment(2) == 'emails_stat') : ?> class="active"<?php endif; ?>>
								<a href="<?php echo base_url('business_intelligence/emails_stat'); ?>">
									<i class="fa fa-angle-right"></i>
									<span>Email Statistics</span>
								</a>
							</li>
							<li<?php if ($this->router->fetch_class() == 'business_intelligence' && $this->uri->segment(2) == 'statistic') : ?> class="active"<?php endif; ?>>
								<a href="<?php echo base_url('business_intelligence/statistic'); ?>">
									<i class="fa fa-angle-right"></i>
									<span>Client Referral Statistics</span>
								</a>
							</li>
							<?php /*
								<li<?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'refferals_users') : ?> class="active"<?php endif; ?>>
									<a href="<?php echo base_url('business_intelligence/refferals_users'); ?>">
										<i class="fa fa-angle-right"></i>
										<span>Reff. Users</span>
									</a>
								</li>
								<li<?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'refferals_clients') : ?> class="active"<?php endif; ?>>
									<a href="<?php echo base_url('business_intelligence/refferals_clients'); ?>">
										<i class="fa fa-angle-right"></i>
										<span>Reff. Clients</span>
									</a>
								</li>	
							*/ ?>
						<?php endif; ?>
					</ul>
				</li>
			<?php endif; ?>
			<?php if ($this->session->userdata('user_type') == "admin" || is_cl_permission_all() || is_cl_permission_owner() || $this->session->userdata('RPS_EST') == 1) : ?>
                <li <?php if ($this->uri->segment(1) == 'business_intelligence' && ($this->uri->segment(2) == 'sales')) : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
                    <a href="<?php echo base_url('business_intelligence/sales'); ?>">
                        <i class="fa fa-angle-right"></i>
                        <span>Sales</span>
                    </a>
                </li>
				<li <?php if ($this->uri->segment(1) == 'business_intelligence' && ($this->uri->segment(2) == 'estimates_report' || $this->uri->segment(2) == 'estimates_statistic')) : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
					<a href="<?php echo base_url('business_intelligence/estimates_report'); ?>">                            
						<i class="fa fa-angle-down text"></i>
						<i class="fa fa-angle-up text-active"></i>
						<span>Estimates</span>
					</a>
					<ul class="nav bg">
						<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('RPS_EST') == 1) : ?>
							<li <?php if ($this->router->fetch_class() == 'business_intelligence' && $this->uri->segment(2) == 'estimates_report') : ?> class="active"<?php endif; ?>>
								<a href="<?php echo base_url('business_intelligence/estimates_report'); ?>">
									<i class="fa fa-angle-right"></i>
									<span>Estimates Reports</span>
								</a>
							</li> 
						<?php endif; ?>
						<li <?php if ($this->router->fetch_class() == 'business_intelligence' && $this->uri->segment(2) == 'estimates_statistic') : ?> class="active"<?php endif; ?>>
							<a href="<?php echo base_url('business_intelligence/estimates_statistic'); ?>">
								<i class="fa fa-angle-right"></i>
								<span>Estimators Statistic</span>
							</a>
						</li>
						<?php /*<li <?php if ($this->router->fetch_class() == 'business_intelligence' && $this->uri->segment(2) == 'new_estimates_statistic') : ?> class="active"<?php endif; ?>>
							<a href="<?php echo base_url('business_intelligence/new_estimates_statistic'); ?>">
								<i class="fa fa-angle-right"></i>
								<span>Estimators Statistic <label style="color:#FF0000;">(New!)</label></span>
							</a>
						</li>
                        */ ?>
					</ul>
				</li>
			<?php endif; ?>
			<?php if ($this->session->userdata('user_type') == "admin"  || is_cl_permission_all()  || $this->session->userdata('RPS_WO') == 1) : ?>
				<li <?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'workorders_report') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>  
					<a href="<?php echo base_url('business_intelligence/workorders_report'); ?>">                            
						<i class="fa fa-angle-down text"></i>
						<i class="fa fa-angle-up text-active"></i>
						<span>Workorders</span>
					</a>
					<ul class="nav bg">
						<li <?php if ($this->router->fetch_class() == 'business_intelligence' && $this->uri->segment(2) == 'workorders_report') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
							<a href="<?php echo base_url('business_intelligence/workorders_report'); ?>">
								<i class="fa fa-angle-right"></i>
								<span>Workorders Reports</span>
							</a>
						</li>
                        <li <?php if ($this->router->fetch_class() == 'business_intelligence' && $this->uri->segment(2) == 'incidents') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
                            <a href="<?php echo base_url('business_intelligence/incidents'); ?>">
                                <i class="fa fa-angle-right"></i>
                                <span>Near Miss / Incidents</span>
                            </a>
                        </li>
					</ul>
				</li>
			<?php endif; ?>
			<?php if ($this->session->userdata('user_type') == "admin"  || is_cl_permission_all()  || $this->session->userdata('RPS_IN') == 1) : ?>
				<li <?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'invoices_report') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>  
					<a href="<?php echo base_url('business_intelligence/invoices_report'); ?>">                            
						<i class="fa fa-angle-down text"></i>
						<i class="fa fa-angle-up text-active"></i>
						<span>Invoices</span>
					</a>
					<ul class="nav bg">
						<li <?php if ($this->router->fetch_class() == 'business_intelligence' && $this->uri->segment(2) == 'invoices_report') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
							<a href="<?php echo base_url('business_intelligence/invoices_report'); ?>">
								<i class="fa fa-angle-right"></i>
								<span>Invoices Reports</span>
							</a>
						</li>
					</ul>
				</li>
			<?php endif; ?>
			<?php if ($this->session->userdata('user_type') == "admin" && config_item('gps_enabled')) : ?>
				<li <?php if ($this->uri->segment(1) == 'business_intelligence' && ($this->uri->segment(2) == 'get_gps_report' || $this->uri->segment(2) == 'distance_report')) : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>  
					<a href="<?php echo base_url('business_intelligence/get_gps_report'); ?>">                            
						<i class="fa fa-angle-down text"></i>
						<i class="fa fa-angle-up text-active"></i>
						<span>Equipment</span>
					</a>
					<ul class="nav bg">
						<li<?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'get_gps_report') : ?> class="active"<?php endif; ?>>
							<a href="<?php echo base_url('business_intelligence/get_gps_report'); ?>">
								<i class="fa fa-angle-right"></i>
								<span>Equipment GPS Data</span>
							</a>
						</li>
						<li<?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'distance_report') : ?> class="active"<?php endif; ?>>
							<a href="<?php echo base_url('business_intelligence/distance_report'); ?>">
								<i class="fa fa-angle-right"></i>
								<span>Eq Distance Report</span>
							</a>
						</li>
					</ul>
				</li>
			<?php endif; ?>
			<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('SCHD') == 1) : ?>
				<li <?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'schedule_report') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>  
					<a href="<?php echo base_url('business_intelligence/schedule_report'); ?>">                            
						<i class="fa fa-angle-down text"></i>
						<i class="fa fa-angle-up text-active"></i>
						<span>Schedule</span>
					</a>
					<ul class="nav bg">
						<li<?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'schedule_report') : ?> class="active"<?php endif; ?>>
							<a href="<?php echo base_url('business_intelligence/schedule_report'); ?>">
								<i class="fa fa-angle-right"></i>
								<span>Schedule Report</span>
							</a>
						</li>
					</ul>
				</li>
			<?php endif; ?>
			<?php if ($this->session->userdata('user_type') == "admin"/* || $this->session->userdata('RPS_GEN') == 1*/) : ?>
				<li <?php if ($this->uri->segment(1) == 'business_intelligence' && ($this->uri->segment(2) == 'general' || $this->uri->segment(2) == 'client_payments')) : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>  
					<a href="<?php echo base_url('business_intelligence/general'); ?>">                            
						<i class="fa fa-angle-down text"></i>
						<i class="fa fa-angle-up text-active"></i>
						<span>Accounting</span>
					</a>
					<ul class="nav bg">
						<li <?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'general') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
							<a href="<?php echo base_url('business_intelligence/general'); ?>">
								<i class="fa fa-angle-right"></i>
								<span>General</span>
							</a>
						</li> 
						<?php if ($this->session->userdata('user_type') == "admin") : ?>
							<li <?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'client_payments') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
								<a href="<?php echo base_url('business_intelligence/client_payments'); ?>">
									<i class="fa fa-angle-right"></i>
									<span>Client Payments</span>
								</a>
							</li>
						<?php endif; ?>
					</ul>
				</li>
			<?php endif; ?>
			<?php if ($this->session->userdata('user_type') == "admin"  || $this->session->userdata('UHR') == 1) : ?>
				<li <?php if ($this->uri->segment(1) == 'business_intelligence' && ($this->uri->segment(2) == 'users_statistics' || $this->uri->segment(2) == 'history' || $this->uri->segment(2) == 'activity' || $this->uri->segment(2) == 'payroll_statistics')) : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>  
					<a href="<?php echo base_url('business_intelligence/users_statistics'); ?>">                            
						<i class="fa fa-angle-down text"></i>
						<i class="fa fa-angle-up text-active"></i>
						<span>Personnel</span>
					</a>
					<ul class="nav bg">
						<li <?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'users_statistics') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
							<a href="<?php echo base_url('business_intelligence/users_statistics'); ?>">
								<i class="fa fa-angle-right"></i>
								<span>Users Statistic</span>
							</a>
						</li> 
						<?php if ($this->session->userdata('user_type') == "admin") : ?>
							<li <?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'gps_tracking') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
								<a href="<?php echo base_url('business_intelligence/gps_tracking'); ?>">
									<i class="fa fa-angle-right"></i>
									<span>GPS tracking</span>
								</a>
							</li> 
							<li <?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'history') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
								<a href="<?php echo base_url('business_intelligence/history'); ?>">
									<i class="fa fa-angle-right"></i>
									<span>Users History Log</span>
								</a>
							</li> 
							<li <?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'activity') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
								<a href="<?php echo base_url('business_intelligence/activity'); ?>">
									<i class="fa fa-angle-right"></i>
									<span>Users Login Activity</span>
								</a>
							</li> 
							<li <?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'payroll_statistics') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
								<a href="<?php echo base_url('business_intelligence/payroll_statistics'); ?>">
									<i class="fa fa-angle-right"></i>
									<span>Support vs Field Payrol Report</span>
								</a>
							</li>
						<?php endif; ?>
						<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('CRW') == 1) : ?>
							<li<?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'crews_statistic') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
								<a href="<?php echo base_url('business_intelligence/crews_statistic'); ?>">
									<i class="fa fa-angle-right"></i>
									<span>Crews Statistic</span>
								</a>
							</li>
						<?php endif; ?>
						<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('UHR') == 1) : ?>
							<li<?php if ($this->uri->segment(1) == 'business_intelligence' && $this->uri->segment(2) == 'absent_days') : ?> class="active"<?php else : ?> class="inactive"<?php endif; ?>>
								<a href="<?php echo base_url('business_intelligence/absent_days'); ?>">
									<i class="fa fa-angle-right"></i>
									<span>Absent Days Stat</span>
								</a>
							</li>
							<?php endif;?>
					</ul>
				</li>
			<?php endif; ?>
		</ul>
	</li>
	<!---END Reports Module- -->
<?php endif; ?> 

<?php /*if($this->session->userdata('user_type') != 'employee' && $this->session->userdata('STP') != 3) : ?>
<li>
	<a href="<?php echo base_url('info'); ?>">
		<i class="fa fa-info icon"><b class="bg-info"></b></i>
		<span class="pull-right">
			<i class="fa fa-angle-down text"></i>
			<i class="fa fa-angle-up text-active"></i>
		</span>
		<span>Info</span>
	</a>
	
		<ul class="nav lt" style="display: none;">
			<li<?php if ($this->uri->segment(1) == 'info' && !$this->uri->segment(2)) : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('info'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Trees</span>
				</a>
			</li>
		
		</ul>
</li>
<?php endif;*/ ?>

<?php if(1 == 0) : ?>
<li <?php if ($this->uri->segment(1) == 'refferals' && $this->uri->segment(2)) : ?>class="active"<?php endif; ?>>
	<a href="<?php //echo base_url('refferals'); ?>">
		<i class="fa fa-info icon"><b class="bg-success"></b></i>
		<span class="pull-right">
			<i class="fa fa-angle-down text"></i>
			<i class="fa fa-angle-up text-active"></i>
		</span>
		<span>Refferals</span>
	</a>
	
		<ul class="nav lt" style="<?php if ($this->uri->segment(1) == 'refferals' && $this->uri->segment(2)) : ?>display: block;<?php else : ?>display: none;<?php endif; ?>">
			<li<?php if ($this->uri->segment(1) == 'refferals' && $this->uri->segment(2) == 'clients') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('refferals/clients'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Clients</span>
				</a>
			</li>
			<li<?php if ($this->uri->segment(1) == 'refferals' && $this->uri->segment(2) == 'users') : ?> class="active"<?php endif; ?>>
				<a href="<?php echo base_url('refferals/users'); ?>">
					<i class="fa fa-angle-right"></i>
					<span>Users</span>
				</a>
			</li>
		
		</ul>
</li>
<?php endif; ?>
<?php if($this->session->userdata('IMP_CT') == 1 || $this->session->userdata('user_type') == "admin") : ?>
<li>
	<a href="<?php echo base_url('notebook'); ?>">
		<i class="fa fa-phone icon"><b class="bg-success"></b></i>
		<span>Important Contacts</span>
	</a>
</li>
<?php endif; ?>
</ul>
</nav>
</div>
</section>
<footer class="footer lt hidden-xs b-t b-light">
	<a href="#nav" data-toggle="class:nav-xs" class="pull-right hidden-sm hidden-xs btn btn-sm btn-default btn-icon<?php if ($this->uri->segment(1) == 'schedule' || $this->uri->segment(1) == 'stumps' || $this->uri->segment(2) == 'history' || ($this->session->userdata('user_type')=='user' && $this->session->userdata('worker_type') == 1)) : ?> active<?php endif; ?>">
		<i class="fa fa-angle-left text"></i>
		<i class="fa fa-angle-right text-active"></i>
	</a>
</footer>
</section>
<script>
	$('#nav').find('.nav').find('a[href="' + baseUrl + '<?php echo $this->uri->segment(1); ?>"]').addClass('active').parent().addClass('active').children('.nav.lt').show();
	$(document).ready(function(){
		$.ajax({
			type: 'POST',
			url: baseUrl + 'dashboard/ajax_counters',
			global: false,
			success: function(resp){
				if(resp.status == 'error')
				{
					//any...
				}
				else
				{
					if(resp.counters)
					{
						$.each(resp.counters, function(key, val){
							if(val)
							{
								$('#' + key).text(val);
								$('#' + key).fadeIn('slow');
							}
						});
					}
				}
				return false;
			},
			dataType: 'json'
		});
	});
</script>
</aside>
    
