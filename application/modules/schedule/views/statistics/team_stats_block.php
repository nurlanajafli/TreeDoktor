<div class="one-team-stat-block pull-left" data-team-id="{{:team_id}}" style="flex: 0 0 {{:sectionWidth}}px;width:{{:sectionWidth}}px;">
    {{if team_id!=0}}
    <div data-team-id="{{:team_id}}" class="team-amount" placeholder="Team Amount...">{{:~currency_format(team_amount, true)}}</div>
    <div data-team-id="{{:team_id}}" class="team-hours th-sortable" <?php /*data-toggle="popover1" data-html="true" data-placement="top" data-container="body" data-content="<div class='form-group m-b-none' style='width: 140px;'><input style='width: 100px;' class='form-control inline teamManHr-{{:team_id}}' type='text' name='team_m_hr' value='{{:team_man_hours}}'><button class='btn btn-xs btn-success m-l-sm changeMHr' data-team-id='{{:team_id}}'><i class='fa fa-check'></i></button></div>"*/?> data-original-title="" title="">
        {{if team_man_hours!=undefined}}
        <span class="teamManHoursText">{{:team_man_hours}}</span> mh ({{:team_estimated_hours}})
        {{/if}}
    </div>
    <div class="teams-stat-block" data-stat-team-id="{{:team_id}}" style="{{if bg_color!=false}}background:{{:bg_color}};{{/if}}">
        <div class="teams-stat-overlay {{if team_closed==1}}show{{/if}}"><div>{{if team_closed==0}}Click To Lock{{else}}Click To Unlock{{/if}}</div></div>
        <table class="text-white" width="100%">
            <tbody>
                <tr>
                    <td colspan="3" width="100%" class="text-center" style="border: 1px solid #fff;">
                        EST- <span class="estimators">{{:estimators}}</span> / TL - <span class="team-leader">
                            {{if team_leader}}{{:team_leader.full_name}}{{else}}N/A{{/if}}</span>
                    </td>
                </tr>
                <tr>
                    <td width="33%" class="text-center" style="border-bottom: 1px solid #fff;border-right: 1px solid #fff;border-left: 1px solid #fff;">
                        EST
                    </td>
                    <td width="33%" class="text-center" style="border-bottom: 1px solid #fff;border-right: 1px solid #fff;">
                        ACT
                    </td>
                    <td width="33%" class="text-center hidden-xs hidden-sm" style="border-bottom: 1px solid #fff;border-right: 1px solid #fff;">
                        %
                    </td>
                </tr>
                <tr>
                    <td class="text-center" style="white-space: nowrap;border-bottom: 1px solid #fff;border-right: 1px solid #fff;border-left: 1px solid #fff;">
                        <span class="estimated-amount">
                            {{if team_estimated_amount}}
                                {{:~currency_format(team_estimated_amount)}}
                            {{else}}
                                N/A
                            {{/if}}
                        </span><br>
                        <span class="estimated-manhours">
                            {{if team_estimated_hours}}{{:team_estimated_hours}} mhr.{{else}}N/A{{/if}}
                        </span>
                    </td>
                    <td class="text-center" style="border-bottom: 1px solid #fff;border-right: 1px solid #fff;white-space: nowrap;">
                        <span class="actual-amount">
                            {{if actual_team_amount}}
                            {{:~currency_format(actual_team_amount, true)}}
                            {{else}}
                            N/A
                            {{/if}}
                        </span><br>
                        <span class="actual-manhours">{{if team_man_hours}}{{:team_man_hours}} mhr.{{else}}N/A{{/if}}</span>
                    </td>
                    <td class="text-center hidden-xs hidden-sm" style="border-bottom: 1px solid #fff;border-right: 1px solid #fff; padding-left: 2px;" valign="top">
                        <span class="amount-productivity">{{if amountProd!==false}}{{:amountProd}}%{{else}}N/A{{/if}}</span>
                    </td>
                </tr>
                <tr>
                    <td class="text-center" style="border-right: 1px solid #fff;border-left: 1px solid #fff;border-bottom: 1px solid #fff;">
                        <span class="estimated-per-hour">{{if estimated_per_hour}}{{:~currency_format(estimated_per_hour)}}{{else}}N/A{{/if}}</span>
                    </td>
                    <td class="text-center" style="border-right: 1px solid #fff;border-bottom: 1px solid #fff;">
                        <span class="actual-per-hour">{{:actual_per_hour}}</span>
                    </td>
                    <td class="text-center hidden-xs hidden-sm" style="border-right: 1px solid #fff;border-bottom: 1px solid #fff;">
                        <span class="per-hour-productivity">{{if perHourProd!==false}}{{:perHourProd}}%{{else}}N/A{{/if}}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    {{/if}}
</div>