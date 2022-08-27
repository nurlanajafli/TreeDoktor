
	<script id="online-workers-tmp" type="text/x-jsrender">
		<li>
			{{if available}}
				<a href="#" class="forward-in-support" title="Online" data-contact_uri="{{:contact_uri}}">
					<i class="fa fa-circle text-success text-xs"></i>&nbsp;{{:friendlyName}}
				</a>
			{{else}}
				<a href="#" class="disabled" title="Offline" data-contact_uri="{{:contact_uri}}">
					<i class="fa fa-circle text-danger text-xs"></i>&nbsp;{{:friendlyName}}
				</a>
			{{/if}}
		</li>
	</script>
	<script id="online-workers-empty-tmp" type="text/x-jsrender">
		<li>{{:message}}</li>
	</script>


	<script id="contacts-tmp" type="text/x-jsrender">
		<li>
			<a href="#" class="forward-in-number" title="Online" data-number="{{:number}}">
				<i class="fa fa-circle text-success text-xs"></i>&nbsp;{{:name}}<div>&nbsp;<small>{{:number}}</small></div>
			</a>
		</li>
	</script>
	<script id="contacts-empty-tmp" type="text/x-jsrender">
		<li>{{:message}}</li>
	</script>