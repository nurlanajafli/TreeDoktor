{{if events.length==0 }}
<h3 class="text-muted text-center text-muted p-top-30 m-top-30" style="min-height: 250px">
    <i class="fa fa fa-meh-o text-primary fa-3x animated fadeInDown" style="color: #65bd776b"></i><br><br>
    <span style="font-size: 18px">
        {{if error!=undefined}}
        <i class="fa fa-info-circle text-danger"></i>&nbsp;Please, check office addresse in <a target="_blank" href="/brands/{{:~defaultBrandId()}}">{{:~defaultBrandName()}}</a>. Thanks
        {{else}}
            <i class="fa fa-info-circle text-primary"></i>&nbsp;Please, create new event. Thanks
        {{/if}}
    </span>
</h3>
{{else}}
    {{if team.team_route_optimized != undefined && team.team_route_optimized == 1}}
    <h3 class="text-muted text-center text-muted p-top-30 m-top-30" style="min-height: 250px">
        <i class="fa fa-check-circle text-primary fa-3x animated fadeInDown" style="color: #65bd776b"></i><br><br>
        <span style="font-size: 18px"><i class="fa fa-truck text-primary animated fadeInRightBig"></i>&nbsp;Route already optimized. Thanks</span>
    </h3>
    {{else}}
    <form data-type="ajax" data-url="/schedule/schedule/optimizeRoute" data-callback="ScheduleUnit.optimizeRouteSuccess">
        <div class="text-center">
        <button type="submit" class="btn btn-primary bg-white br-radius-50 primary-hover save-optimized-routes">
            <i class="fa fa-truck"></i>&nbsp;Optimize route
        </button>
        </div>
    <div class="timeline">

        <article class="timeline-item alt">
            <div class="timeline-caption p-10 p-right-25">
                <div class="position-relative schedule-optimization-route-event-body">
                    <span class="timeline-icon"><i class="fa fa-home time-icon inline-block bg-dark"></i></span>
                </div>
            </div>
        </article>

        {{for events}}
        <input type="hidden" name="event_id[]" value="{{:id}}">
        <input type="hidden" name="event_start[]" value="{{:~dateFormat(start_date, 'YYYY-MM-DD HH:mm')}}">
        <input type="hidden" name="event_end[]" value="{{:~dateFormat(end_date, 'YYYY-MM-DD HH:mm')}}">
        <input type="hidden" name="event_team_id" value="{{:#get("root").data.team.team_id}}">

        <article class="timeline-item alt">
            <div class="timeline-caption p-10 p-right-25">
                <div class="position-relative schedule-optimization-route-event-body">
                    <div class="p-10 p-right-25">
                        <span class="arrow right"></span>
                        <span class="timeline-icon"><i class="fa fa-truck time-icon bg-primary"></i></span>
                        <span class="timeline-date">{{:~dateFormat(start_date, ~getTimeFormat())}} - {{:~dateFormat(end_date, ~getTimeFormat())}}</span>
                        <p><label class="text-dark">{{:workorder_no}}</label>, <i class="fa fa-user text-muted"></i>&nbsp;{{:client.client_name}}</p>
                        <h5>
                            <i class="fa fa-map-marker text-danger"></i>&nbsp;{{if estimate.lead }}{{:estimate.lead.lead_address}}, {{:estimate.lead.lead_city}}{{/if}}
                        </h5>
                        <p>{{:total_for_services}}, {{:total_service_time}}mhr. ({{:total_hours}} hr.) </p>
                    </div>
                </div>
            </div>
        </article>
        {{/for}}

        <div class="timeline-footer"><a href="#"><i class="fa fa-home time-icon inline-block bg-dark"></i></a></div>
    </div>

    </form>
    {{/if}}
{{/if}}