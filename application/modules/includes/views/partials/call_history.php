<div id="call-history">
	<section class="scrollable">
		<ul class="nav nav-tabs call-tabs" data-type="calls" data-action="stop">
			<li class="active width-25 text-center text-ellipsis"  data-id="0">
				<a href="#allCalls" data-toggle="tab">All Calls</a>
			</li>
			<li class="width-25 text-center text-ellipsis" data-id="<?php echo $this->session->userdata('user_id') ? $this->session->userdata('user_id') : $user_id; ?>">
				<a href="#myCalls" data-toggle="tab">My Calls</a>
			</li>
			<li class="width-40 text-center text-ellipsis" data-id="-1">
				<a href="#voicemails" data-toggle="tab">Voicemails</a>
			</li>
			<li class="width-10 text-center text-ellipsis" data-id="-1">
				<a href="#searchCalls" data-toggle="tab"><i class="fa fa-fw fa-search"></i></a>
			</li>
		</ul>
		<div class="tab-content">
			
			<div class="tab-pane active" id="allCalls">
				<ul class="list-group no-radius m-b-none m-t-n-xxs list-group-alt list-group-lg" id="all-calls-history"></ul>
			</div>
			<div class="tab-pane" id="myCalls">
				<ul class="list-group no-radius m-b-none m-t-n-xxs list-group-alt list-group-lg" id="my-calls-history"></ul>
			</div>
			<div class="tab-pane" id="voicemails">
				<ul class="list-group no-radius m-b-none m-t-n-xxs list-group-alt list-group-lg" id="voices-history"></ul>
			</div>
			<div class="tab-pane" id="searchCalls">
				<form class="p-10 b-b form-group animated fadeInUp m-n" id="searchInHistoryForm">
					<div class="pull-left w-80">
						<input type="text" class="form-control searchInHistoryValue" data-placement="top" title="Min. 3 Numbers">
					</div>
					<div class="pull-left w-20">
						<span class="input-group-btn">
							<button class="btn btn-default w-100 searchInHistory" type="submit">Go!</button>
	  					</span>
					</div>
					<div class="clear"></div>
				</form>
				<ul class="list-group no-radius m-b-none m-t-n-xxs list-group-alt list-group-lg" id="search-history">
					
				</ul>
			</div>
		</div>
	
	</section>
</div>
<?php $this->load->view('partials/calls_history_list'); ?>