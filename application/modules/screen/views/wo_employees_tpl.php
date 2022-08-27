<ul class="dropdown-menu pull-right text-left emp-dropdown animated fadeInLeft"
    data-crew_id="" style="width: 630px;height: 300px; overflow-y: scroll;">
	<a href="#" class="btn btn-xs btn-danger no-shadow deleteTeam" style="position: absolute;top: 0px;right: 0px;"><i class="fa fa-trash-o"></i></a>
	<div class="arrow top" style=""></div>
	<div style="width:60%" class="pull-left">
		<li class="crewInfo"></li>
		<div class="line line-dashed line-lg line-members"></div>
		<?php $this->load->view('free_employees_label'); ?>
	</div>
	<div style="width:40%" class="pull-left">
		<li class="eqInfo">Equipment:</li>
		<div class="line line-dashed line-lg line-items"></div>
		<?php $this->load->view('free_items_label'); ?>
	</div>
	<div class="clear"></div>
</ul>