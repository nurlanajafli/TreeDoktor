<script id="agents-num-tmp" type="text/x-jsrender">
	{{:agents_count}}
</script>
<div class="agents-num" id="agents-num" data-toggle="dropdown">-</div>Agents


<script id="agents-list-tmp" type="text/x-jsrender">
	<li class="{{if available!=true}}disabled{{/if}}"><a href="#" class="{{if available==true}}{{:linkClass}}{{else}}disabled{{/if}} clearfix" data-worker_sid="{{:sid}}" data-contact_uri="{{:contact_uri}}" {{:available}}>
        <span class="pull-left agent-name">{{:friendlyName}}</span>
        <i class="fa fa-circle text-xs pull-right m-t-xs text-{{if available==true }}success{{else}}danger{{/if}}"></i>
    </a></li>
</script>

<script id="agents-list-empty-tmp" type="text/x-jsrender">
	<li>{{:message}}</li>
</script>

<ul class="dropdown-menu animated fadeInRight pull-right" style="left: 1px;" id="agents-list-container">
	<span class="arrow top"></span>
</ul>