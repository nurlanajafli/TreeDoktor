<div class="menu m-n p-n">
    <?= $this->load->view('_partials/section_tree_projects'); ?>

    <section class="panel panel-default scrollable tree-list" hidden>
        <div class="row">
            <div class="col-md-6 p-top-20 p-bottom-20">
                <button class="btn back-to-projects m-left-10" style="border-radius: 8px; background-color: white; color: #729C44; border: 1px solid  #729C44;"><i class="fa fa-mail-reply"></i> Back to all list</button>
            </div>
            <div class="col-md-6 text-right p-top-20 p-bottom-20">
                <button class="btn project-delete m-right-10" style="border-radius: 8px; background-color: white; color: #CC806A; border: 1px solid  #CC806A;"><i class="fa fa-trash-o"></i> Delete</button>
            </div>
            <div class="col-md-12 p-bottom-20 edit-project" style="padding-left: 25px; padding-right: 25px;">

            </div>
        </div>
        <ul class="list-group" id="tree-list" style="cursor: pointer">
            <li class="list-group-item">
                <p class="text-muted h4 text-center">Tree list is empty</p>
            </li>
        </ul>
    </section>
	<div style="position: absolute;  bottom: 50px; height: 33px; width: 100%; padding-right: 10px;">
	</div>
</div>

<button type="button" class="btn btn-sm btn-primary icon-menu">
	<i class="fa fa-angle-double-left"></i>
	<i class="glyphicon glyphicon-tree-deciduous"></i>
</button>

<button type="button" class="btn btn-sm btn-primary icon-close hidden">
    <i class="fa fa-angle-double-right"></i>
    <i class="glyphicon glyphicon-tree-deciduous"></i>
</button>


<script async src="<?php echo base_url('assets/js/StyledMarker.js'); ?>"></script>
<script async src="<?php echo base_url('assets/js/label.js'); ?>"></script>


<script type="text/x-jsrender" id="tree-list-tmp-emp">
	<li class="list-group-item">
		<p class="text-muted h4 text-center">Tree list is empty</p>
	</li>
</script>

<script type="text/x-jsrender" id="tree-list-tmp">

<li class="list-group-item tree-item" data-lat="{{:ti_lat}}" data-lng="{{:ti_lng}}">
	    <div class="row edit-tree-{{:ti_map_type}}" data-ti_id="{{:ti_id}}" data-ti_lat="{{:ti_lat}}" data-ti_lng="{{:ti_lng}}" data-ti_client_id="{{:ti_client_id}}" data-ti_tree_number="{{:ti_tree_number}}" data-ti_tree_type="{{:ti_tree_type}}" data-ti_prune_type_id="{{:ti_prune_type_id}}">
            <div class="col-lg-1 col-md-1 col-xs-1 text-center centerBlock " >
                <label class="custom-radio">
                    <input type="checkbox" class="treeIdChecker" data-ti_id="{{:ti_id}}" name="ti_ids_[{{:ti_id}}]" checked disabled>
                    <span>
                        <i class="fa fa-check true"></i>
                        <!--i class="fa fa-check-square true"></i>
                        <i class="fa fa-check-square-o true-ever"></i>
                         <i class="fa fa-square-o false"></i>
                         <i class="fa fa-minus-square-o false-ever"></i-->
                    </span>
	    		</label>
	    	</div>
	    	<div class="col-lg-1 col-md-1 col-xs-1 text-center centerBlock" >
	    		{{if ti_file!=NULL }}
		    	<img src="{{:ti_file}}" alt="{{:ti_tree_type}}" class="img-circle" width="90%" height="auto">
			    {{/if}}
	    	</div>
	    	<div class="col-lg-5 col-md-5 col-sm-5 m-left-0 m-right-0 p-left-0 p-right-0">
		    	<div class="row" style="line-height:20px">
			    	<div class="col-lg-12 col-md-12 col-sm-12 text-success ">
			    		Tree #{{:ti_tree_number}} <span class="label label-warning" style="background:{{:priority_color}};" >{{:ti_tree_priority}}</label>
			    	</div>
			    	<div class="col-lg-12 col-md-12 col-sm-12" style="word-break: break-word;">
			    		<div>Work type: {{:~work_types_string(work_types)}}</div>
					</div>
				</div>
		    </div>

		    <div class="col-lg-5 col-md-5 col-sm-5 m-left-0 m-right-0 p-left-0 p-right-0">
		        <div class="col-lg-12 col-md-12 col-sm-12">Cost: {{:~currency_format(ti_cost)}}</div>
			    <div class="col-lg-12 col-md-12 col-sm-12">Stump: {{:~currency_format(ti_stump_cost)}}</div>
		    </div>

		    <!--div class="col-md-12 visible-md m-top-5" style="word-break: break-word;">
	    		<div class="h5"><strong><span class="h5 text-info">#{{:ti_tree_number}}</span>&nbsp;{{:~showString((tree_type)?tree_type.trees_name_eng:'')}}{{if tree_type && tree_type.trees_name_lat}}&nbsp;({{:tree_type.trees_name_lat}}){{/if}} {{:~work_types_string(work_types, false)}}</strong></div>
			</div>
		    <div class="col-md-12 col-sm-10 hidden-sm visible-md">
			    <small class="block">{{:ti_remark}}</small>
			</div-->


		    <form class="delete-{{:ti_map_type}}-item" id="delete-{{:ti_map_type}}-item-{{:ti_id}}" data-type="ajax" data-url="<?php echo site_url('tree_inventory/delete'); ?>" data-callback="TreeInventory{{:~topFirstChar(ti_map_type)}}.delete_callback" data-global="false" data-runbefore="TreeInventory{{:~topFirstChar(ti_map_type)}}.before_delete">
				<input type="hidden" name="ti_id" value="{{:ti_id}}">
				<input type="hidden" name="ti_client_id" value="<?php echo $client->client_id; ?>">
				{{if ti_lead_id!=undefined}}
				<input type="hidden" name="ti_lead_id" value="{{:ti_lead_id}}">
				<input type="hidden" name="ti_tis_id" value="{{:ti_tis_id}}">
				{{/if}}
				<button type="button" class="btn confirmDelete text-success" data-confirmation-massage="Are you sure to delete <span ># {{:ti_tree_number}}</span> ?" data-yes-text="Yes" data-submit-form="#delete-{{:ti_map_type}}-item-{{:ti_id}}"><i class="fa fa-trash-o"></i></button>
			</form>
	    </div>
    
</li>
</script>

<script type="text/javascript">
	<?php if(isset($tree_inventory) && !empty($tree_inventory)): ?>
		window.tree_inventory_list = '<?php echo json_encode($tree_inventory, JSON_HEX_QUOT | JSON_HEX_APOS); ?>';
    <?php endif; ?>
    	
    	window.ti_map_type = '<?php echo $ti_map_type; ?>';
		window.priority_color =  JSON.parse('<?php echo (!empty($priority_color))?json_encode($priority_color):"[]"; ?>');
		window.client_address = '<?php echo addslashes($client_address); ?>';
        window.home_address = '<?php echo addslashes($home_address); ?>';

        window.work_types = <?php echo (isset($work_types) && !empty((array)$work_types))?json_encode($work_types):json_encode([]); ?>;
		
		window.client_id = <?php echo $client->client_id; ?>;
		window.tax_rate = <?php echo getDefaultTax()['rate']; ?>

        let itemsForSelect2 = <?= getCategoriesItemsForSelect2() ?>;
        var selectTags = itemsForSelect2.services;
        var selectTagsProducts = itemsForSelect2.products;
        var selectTagsBundles = itemsForSelect2.bundles;
        $('#tree-list-project li:first').css('margin-top', '0px');
        let tree_project_tmp = <?= $tree_project_tmp ?>;
        let date_now = '<?= date(getDateFormat()); ?>';
</script>

<div id="new_lead" class="modal fade" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n" style="overflow-x: hidden;">
        	<form id="create-lead-modal" data-type="ajax" data-global="false" data-url="<?php echo base_url('leads/create_lead'); ?>" data-callback="TreeInventoryMap.copy_tree_inventory">
				<?php echo $this->load->view('leads/new_lead_modal'); ?>
			</form>
		</div>
    </div>
</div>

<div id="copy-tree-inventory-modal" class="modal fade" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n" style="overflow-x: hidden;">
        	<header class="panel-heading h4"><i class="fa fa-user text-success">&nbsp;</i><?php echo $client_data->client_name; ?></header>

        	<div class="list-group">
  				<?php 
                /*echo "<pre>";
                var_dump($client_leads);
                die;*/
                if(isset($client_leads_not_confirmed) && count($client_leads_not_confirmed)>1): ?>
					<?php foreach($client_leads_not_confirmed as $key => $lead): ?>

			  			<?php if(isset($ti_lead_id) && $ti_lead_id!=$lead->lead_id): ?>
						
						<input type="radio" name="copy_to_lead" value="Value2" id="copy-to-<?php echo $client->client_id; ?>-<?php echo $lead->lead_id; ?>" data-lead_id="<?php echo $lead->lead_id; ?>" data-client_id="<?php echo $client->client_id; ?>"/>
						<label class="list-group-item" for="copy-to-<?php echo $client->client_id; ?>-<?php echo $lead->lead_id; ?>">
							<strong class="text-success">#<?php echo element('lead_no', (array)$lead, ''); ?></strong>&nbsp;&nbsp;<?php echo lead_address((array)$lead); ?>&nbsp;&nbsp;<i class="text-success fa fa-map-marker"></i></label>

			  			<?php endif; ?>
						
					<?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center">
                    <h4 class="text-muted text-center">Leads not exist</h4>
                    <a href="#new_lead" data-dismiss="modal" data-toggle="modal" class="btn btn-primary" data-backdrop="static" data-keyboard="false">Copy to new lead&nbsp;<i class="fa fa-copy"></i></a>
                    </div>
				<?php endif; ?>
			  
			  
			</div>

        	<div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                <button type="submit" name="submit" onclick="$('#copy-tree-inventory').trigger('submit');" class="btn btn-success">Copy&nbsp;<i class="fa fa-copy"></i></button>
            </div>
		</div>
    </div>
</div>
<?php $this->load->view('clients/appointment/schedule_appointment_modal'); ?>

<style type="text/css">
#copy-tree-inventory-modal .modal-footer {
    padding: 5px 20px 20px;
    margin-top: 15px;
    text-align: right;
    border-top: 0;
}

.list-group-item {
  user-select: none;
}

.list-group input[type="checkbox"] {
  display: none;
}

.list-group input[type="checkbox"] + .list-group-item {
  cursor: pointer;
}

.list-group input[type="checkbox"] + .list-group-item:before {
  content: "\2713";
  color: transparent;
  font-weight: bold;
  margin-right: 1em;
}

.list-group input[type="checkbox"]:checked + .list-group-item {
  background-color: #0275D8;
  color: #FFF;
}

.list-group input[type="checkbox"]:checked + .list-group-item:before {
  color: inherit;
}

.list-group input[type="radio"] {
  display: none;
}

.list-group input[type="radio"] + .list-group-item {
  cursor: pointer;
}

.list-group input[type="radio"] + .list-group-item:before {
  content: "\2022";
  color: transparent;
  font-weight: bold;
  margin-right: 1em;
}

.list-group input[type="radio"]:checked + .list-group-item {
  background-color: #118a13;
  color: #FFF;
}

.list-group input[type="radio"]:checked + .list-group-item:before {
  color: inherit;
}
.centerBlock{
    display:flex;
    justify-content:center;
    align-items:center;
    min-height: 50px;
    margin: 0;
    padding: 0;
}
.centerBlock img{
    margin-left:-10px;
}
.delete-map-item .confirmDelete{
    background:none;
    position:relative;
    top:10px;
}
.custom-radio span i{
    display:none;
}
.custom-radio>input:checked:not(.everSelected)+span i.true{
    display:block;
    color: #8ec165;
}
.custom-radio>input:not(:checked):not(.everUnSelected)+span i.false{
    display:block;
}

.custom-radio>input.everUnSelected+span i.false-ever{
    display:block;
    color: #d43f3a;
}

.custom-radio>input.everSelected+span i.true-ever{
    display:block;
    color: #8ec165;
}

</style>
