<svg xmlns="http://www.w3.org/2000/svg" width="{{:width+28*teams.length}}" veiwBox="0 0 {{:width+28*teams.length}} 55" height="55">
    <defs>
        <filter id="shadow">
            <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="rgba(0, 0, 0)" flood-opacity="0.15" />
        </filter>
        <style>
            .cls {
                fill: #ffffff;
            }
            .cls-font {
                font: 14px Roboto, sans-serif;
            }
            .cls-font-icon {
                font: 12px Roboto, sans-serif;
            }
            .cls-price {
                font-weight: bold;
                fill: #65bd77;
            }
            .cls-text {
                font-weight: lighter;
                fill: #000000;
            }
            .font-red{
                font: 9px Roboto, sans-serif;
                font-weight: bold;
                fill: red;
            }
        </style>
    </defs>
    <g style="filter:url(#shadow);">
        <rect id="right-side" class="cls" x="calc(100% - 34px)" width="24" height="30" rx="8" ry="8" y="10"/>
        <rect id="left-side" class="cls" width="24" rx="8" ry="8" y="10" x="10" height="30"/>
        <rect id="center" class="cls" x="22" width="calc(100% - 44px)" height="30" y="10"/>
        <polygon xmlns="http://www.w3.org/2000/svg" points="35,35 65,35 50,50" style="transform: translate(calc(50% - 50px), 0);" class="cls"/>

        {{if teams.length}}
            {{for teams}}
            <circle r="11" cx="{{:(#getIndex()+1)*28.5}}" cy="25" fill="{{:team.team_color}}" stroke="{{if #get("root").data.current_team_id == team.team_id}}#ffc400{{else}}{{:team.team_color}}{{/if}}" stroke-width="3"/>
            <text x="{{:(#getIndex()+1)*28.5}}" class="cls-font-icon" text-anchor="middle" y="29" font-size="11.5" fill="{{:~getTextColor(team.team_color)}}">{{if team.team_leader.initials!=undefined}}{{:team.team_leader.initials}}{{else}}N/A{{/if}}</text>

            {{if #get("root").data.current_team_id == team.team_id}}
            <text transform="translate(0 33)" stroke="#a57500" stroke-width="1" fill="#ffc400" x="{{:(#getIndex()+1)*38-(#getIndex()*10)}}" y="-14" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="15" text-anchor="middle">&#9733;</text>
            {{/if}}

            <circle r="6" cx="{{:(#getIndex()+1)*38-(#getIndex()*10)}}" cy="35" fill="#ffffff" stroke="red" stroke-width="1"/>
            <text x="{{:(#getIndex()+1)*38-(#getIndex()*10)}}" width="10" y="36" text-anchor="middle" alignment-baseline="middle" font-size="8" class="font-red">{{:pointer}}</text>

            {{/for}}
        {{/if}}

        <text id="_1" data-name="$1" x="{{if teams.length}}{{:(teams.length*28.5)+20}}{{else}}24{{/if}}" y="30px">
            <tspan class="cls-font cls-price">{{:pin_data.price}}</tspan>
            {{if pin_data.crew && pin_data.crew !== ''}}<tspan class="cls-font cls-text">{{:~encodeUTF8string(pin_data.crew)}}</tspan>{{/if}}
        </text>
    </g>
</svg>