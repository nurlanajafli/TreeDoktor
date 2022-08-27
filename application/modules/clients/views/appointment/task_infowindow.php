<div id="{{:task_id}}" class="marker taskMarker infowindow-container" data-task="1" style="overflow: hidden">
    <input type="hidden" name="marker_key" value="{{:marker_key}}">
    <div>
        <strong class="pull-left"><a href="/{{:client_id}}" target='_blank'>{{:cc_name}}</a></strong>
        <label class="label pull-right label-{{if task_status=='new'}}success{{/if}}{{if task_status=='canceled'}}danger{{/if}}{{if task_status=='done'}}default{{/if}}">{{:~toUpperCase(task_status, 0)}}</label>
        <div class="clear"></div>
    </div>
    <div><i class="glyphicon glyphicon-user text-success"></i>&nbsp;Created by:&nbsp;<strong>{{:emp_name}}</strong></div>
    <div><i class="glyphicon glyphicon-user text-success"></i>&nbsp;Assigned to:&nbsp;<strong>{{:firstname}}&nbsp;{{:lastname}}</strong></div>
    <div><i class="glyphicon glyphicon-calendar text-success"></i>&nbsp;Date:&nbsp;{{:task_date}}</div>
    <div><i class="glyphicon glyphicon-calendar text-success"></i>&nbsp;Schedule Date:&nbsp;{{:formated_time_start}} - {{:formated_time_end}} on {{:~format_date(task_start_date)}}</div>
    <div><abbr title="Phone"><i class="glyphicon glyphicon-earphone text-success"></i>&nbsp;Phone: </abbr>{{:cc_phone_view}}</div>
    <div><i class="glyphicon glyphicon-file text-success"></i>&nbsp;Category: {{:category_name}}</div>
    <span>Address:&nbsp;{{:full_address}}<br>{{:task_desc}}</span><br>
</div>
