<script id="calls-history-empty-tmp" type="text/x-jsrender">
	<li class="text-center"><strong>{{:message}}</strong></li>
</script>
<script id="calls-history-list-tmp" type="text/x-jsrender">
	<li class="p-5 list-group-item">
		<div class="row">
			<div class="col-md-1 call-route h3">
				<i class="fa fa-sign-{{if call_route!=0}}in{{else}}out{{/if}} text-{{if call_route!=0}}{{if call_duration>0}}success{{else}}danger{{/if}}{{else}}info{{/if}}"></i>
			</div>

			<div class="col-md-6 call-info">
				<span class="call-agent-name">
					{{if call_route!=0}}
						To: 
					{{else}}
						From: 
					{{/if}}
					{{if firstname}}
						{{:firstname[0].toUpperCase()}}. {{:lastname}}
					{{else}}
						{{:call_to}}
					{{/if}}
				</span>
				<br>
				<strong>
					{{if client_name!=undefined}}
						<a class="text-ul client-iframe" href="<?php echo base_url(); ?>{{:client_id}}">{{:client_name}}</a>
					{{/if}}<br>

					{{if call_route!=0}}
						From: {{:call_from}}
					{{else}}
						To: {{:call_to}}
					{{/if}}
				</strong>
			</div>
			
			<div class="col-md-4 text-center">
				<small class="text-muted">
					
					{{if ~call_dates.is_today(call_date)==true}}
						Today<br>
					{{else}}
						{{:~call_dates.callDateFormat(call_date)}}<br>
					{{/if}}
					
					{{:~call_dates.callTime(call_date)}}<br>
					{{:~call_dates.callDuration(call_duration)}}
					
				</small>
				
				{{if call_voice}}
				<div class="ui360">
					<a href="{{:call_voice}}.wav?{{:~call_dates.callUnixtime()}}" data-title="Play Recording"></a>
				</div>
				{{/if}}
			</div>

			<a href="#" title="Call To Client" class="btn btn-success call-button h3 clear outgoing-call" data-phone="{{if call_route!=0}}{{:call_from}}{{else}}{{:call_to}}{{/if}}">
				<br><i class="fa fa-phone"></i>
			</a>
		</div>
	</li>
</script>


<script id="calls-history-list-tmp" type="text/x-jsrender">
	<li>{{:message}}</li>
</script>
