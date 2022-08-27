<div id="call-history">
	<section class="scrollable">
		<ul class="nav nav-tabs call-tabs" data-type="calls" data-action="stop">
			<li class="active width-25 text-center text-ellipsis"  data-id="0">
				<a href="#tab1" data-toggle="tab">All Calls</a>
			</li>
			<li class="width-25 text-center text-ellipsis" data-id="<?php echo $this->session->userdata('user_id') ? $this->session->userdata('user_id') : $user_id; ?>">
				<a href="#tab2" data-toggle="tab">My Calls</a>
			</li>
			<li class="width-40 text-center text-ellipsis" data-id="-1">
				<a href="#tab3" data-toggle="tab">Voicemails</a>
			</li>
			<li class="width-10 text-center text-ellipsis" data-id="-1">
				<a href="#tab4" data-toggle="tab"><i class="fa fa-fw fa-search"></i></a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="tab1">
				<ul class="list-group no-radius m-b-none m-t-n-xxs list-group-alt list-group-lg">
					<?php $this->load->view('call_history_list'); ?>
				</ul>
			</div>
			<div class="tab-pane" id="tab2">
				<ul class="list-group no-radius m-b-none m-t-n-xxs list-group-alt list-group-lg">
					<?php $my_calls = isset($my_calls) ? $my_calls : array(); ?>
					<?php $this->load->view('call_history_list', array('calls' => $my_calls)); ?>
				</ul>
			</div>
			<div class="tab-pane" id="tab3">
				<ul class="list-group no-radius m-b-none m-t-n-xxs list-group-alt list-group-lg">
					<?php $voices = isset($voices) ? $voices : array(); ?>
					<?php $this->load->view('call_history_list', array('calls' => $voices)); ?>
				</ul>
			</div>
			<div class="tab-pane" id="tab4">
				<ul class="list-group no-radius m-b-none m-t-n-xxs list-group-alt list-group-lg">
					<li>Search Results Must Be Here</li>
				</ul>
			</div>
		</div>
	
	</section>
</div>
