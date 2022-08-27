<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/dialer.css'); ?>">


<div class="phone-overlay animated fadeInRight">
	<div id="softphone" class="softphone">
		<div>
			<a class="badge bg-danger closePhone" href="#" style="padding: 3px 6px;">x</b></a>

			<div id="agent-status-controls" class="clearfix">
				<button class="agent-status ready" disabled="">Ready</button>
				<button class="agent-status not-ready" disabled="">Not Ready</button>
				<div class="agent-status active" style="display: none;">Call In-Progress</div>
			</div><!-- /agent-status -->

			<div id="agent-status">
				<p></p>
			</div><!-- /agent-status -->

			<div class="divider"></div>

			<div id="number-entry">
				<input placeholder="+1 (555) 555-5555">
				<div class="incoming-call-status">Incoming Call</div>
			</div><!-- /number-entry" -->

			<div id="dialer">
				<div id="dialer-container">
					<div class="numpad-container"><div class="number" value="1">1</div><div class="number" value="2">2</div><div class="number" value="3">3</div><div class="number" value="4">4</div><div class="number" value="5">5</div><div class="number" value="6">6</div><div class="number" value="7">7</div><div class="number" value="8">8</div><div class="number" value="9">9</div><div class="number" value="*">&lowast;</div><div class="number" value="0">0</div><div class="number" value="#">#</div>
					</div><!-- /numpad-container -->
				</div><!-- /dialer-container -->
			</div><!-- /dialer -->

			<div id="messages">
				<div id="messages-container">	</div>
				<div id="message-entry">
					<input placeholder="Text Message">
				</div><!-- /message-entry" -->
			</div>



			<div id="action-button-container">
				
				<script id="action-buttons-tmp" type="text/x-jsrender">
					<button class="call {{:call}}">Call</button>
					<button class="send {{:send}}">Send</button>
					<button class="answer {{:answer}}">Answer</button>
					<button class="hangup {{:hangup}}">Hangup</button>
					<button class="wrapup {{:wrapup}}">Wrap Up</button>
					<button class="mute {{:mute}}">Mute</button>
					<?php /*<button class="hold">Hold</button>*/ ?>
					<button class="unhold {{:unhold}}">UnHold</button>
					
					<div class="pull-left w-50">
						<a href="#" id="forward-call-in-worker" class="btn btn-default dropdown-toggle dk mute w-100 {{:forward}}" data-toggle="dropdown" aria-expanded="true">
							<i class="glyphicon glyphicon-share-alt"></i>&nbsp;<i class="glyphicon glyphicon-user"></i>
						</a>
						
				
						<ul class="dropdown-menu animated fadeInRi" id="online-workers" aria-labelledby="forward-call-in-worker"><span class="arrow top"></span></ul>
					</div>
					<div class="pull-left w-50">
						<a href="#" id="forward-call-in-phone" class="btn btn-default dropdown-toggle dk mute w-100 {{:forward}}" data-toggle="dropdown" aria-expanded="true">
							<i class="glyphicon glyphicon-share-alt"></i>&nbsp;<i class="glyphicon glyphicon-phone"></i>
						</a>
						<ul class="dropdown-menu animated fadeInRi" id="users-contacts" aria-labelledby="forward-call-in-phone"><span class="arrow top"></span></ul>
					
					</div>
				</script>
					
				<div id="action-buttons">
				</div><!-- /action-buttons -->

				<?php $this->load->view('phone_supports'); ?>
			</div><!---action-button-containe -->

			<div id="call-data">
				<h3>Caller info</h3>
				<ul class="name"><strong>Name: </strong><span class="caller-name"></span></ul>
				<ul class="phone_number"><strong>Number: </strong><span class="caller-number"></span></ul>
				<ul class="queue"><strong>Queue: </strong><span class="caller-queue"></span></ul>
				<ul class="message"><strong>Message: </strong><span class="caller-message"></span></ul>
			</div><!-- /call-data -->


			<div id="team-status">
				<div class="agents-status">
					<?php $this->load->view('partials/phone_agents'); ?>
				</div>

				
				<script id="queues-count-tmp" type="text/x-jsrender">
					{{if queuesize==undefined }}0{{else}}{{: queuesize}}{{/if}}
				</script>

				<div class="queues-status pull-right"><div id="queues-count-result" class="queues-num" data-toggle="dropdown">0
				</div>In-Queue

					<script id="in-queues-list-tmp" type="text/x-jsrender">
						<li>{{:caller}}<br><a href="#" class="clientLink p-n text-ul" data-clientId="{{:client.client_id }}" style="display: inline-block;">{{:client.client_name}}</a>
						</li>
					</script>

					<script id="in-queues-list-empty-tmp" type="text/x-jsrender">
						<li>{{:message}}</li>
					</script>

					<ul id="in-queues-list" class="dropdown-menu animated fadeInRight pull-right"><li>Queue is empty</li></ul>
				</div>


			</div><!-- /team-status -->
		</div>
		<?php $this->load->view('partials/call_history'); ?>

	</div>
	<!-- /softphone -->
	<div>
		<iframe src="<?php //echo base_url('iframe'); ?>" width="80%" height="100%" class="site-iframe"></iframe>
	</div>

	<div style="clear: both;"></div>
</div>



<script>
	window.worker_sid = "<?php echo $this->session->userdata('twilio_worker_id'); ?>";
	window.workspace_sid = "<?php echo $this->session->userdata('twilio_workspace_id'); ?>";
</script>


<script type="text/javascript" src="<?php echo base_url('assets/js/libs/twilio/twilio.min.js'); ?>"></script>

<script type="text/javascript" src="<?php echo base_url('assets/js/libs/twilio/taskrouter.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/libs/twilio/twilio-rtc-conversations.min.js'); ?>"></script>

<link type="text/css" rel="stylesheet" href="<?php echo base_url('assets/css/flashblock.css'); ?>"/>


<script src="<?php echo base_url('assets/js/libs/socketio/socket.io-1.4.5.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js/modules/client_calls/softphone2.js?'); ?><?php echo time(); ?>"></script>

<?php /*if($this->session->userdata('user_id')==31 || $this->session->userdata('user_id')==44): ?>

<?php else: ?>

<script type="text/javascript" src="<?php echo base_url('assets/js/softphone.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js/reconnecting-websocket.min.js'); ?>"></script> 

<?php endif;*/ ?>



