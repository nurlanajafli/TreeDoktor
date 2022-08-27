<svg xmlns="http://www.w3.org/2000/svg" width="{{if ~getTimeIntFormat()==24}}190{{else}}235{{/if}}" veiwBox="0 0 150 {{:((28*teams.length)+44)}}" height="{{:((28*teams.length)+44)}}">
    <defs>
        <filter id="shadow">
            <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="rgba(0, 0, 0)" flood-opacity="0.15" />
        </filter>
        <style>
            .main-cls{
                fill: #fffbed;
            }
            .cls {

            }
            .cls-font {
                font: 13px Roboto, sans-serif;
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
    <g style="filter:url(#shadow);" class="main-cls">
        <rect fill="#fffddd" id="right-side" class="cls" x="calc(100% - 34px)" width="24" height="{{:(28*teams.length)+8}}" rx="8" ry="8" y="20"/>
        <rect fill="#fffddd" id="left-side" class="cls" width="24" rx="8" ry="8" x="10" height="{{:(28*teams.length)+8}}" y="20"/>
        <rect fill="#fffddd" id="center" class="cls" x="22" width="calc(100% - 44px)" height="{{:(28*teams.length)+8}}" y="20"/>
        <polygon xmlns="http://www.w3.org/2000/svg" y="{{:((28*teams.length)+10)}}" points="35,55 65,55 50,70" style="transform: translate(calc(50% - 50px), {{:(28*(teams.length-1))}}px);" class="cls"/>

        <?php /*
        {{if teams.length}}
        {{for teams}}
        <circle r="11" cx="35" cy="{{:((#getIndex()+1)*28)+10}}" fill="{{:team.team_color}}" stroke="{{if #get("root").data.current_team_id == team.team_id}}#ffc400{{else}}{{:team.team_color}}{{/if}}" stroke-width="3"/>
        <text x="35" y="{{:(((#getIndex()+1)*28)+11)}}" text-anchor="middle" alignment-baseline="middle" font-size="12" fill="{{:~getTextColor(team.team_color)}}">{{:team.team_leader.emailid}}</text>

        {{if #get("root").data.current_team_id == team.team_id}}
        <text transform="translate(0 33)" stroke="#a57500" stroke-width="1" fill="#ffc400" x="44" y="{{:((#getIndex()+1)*28)-30}}" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="15" text-anchor="middle">&#9733;</text>
        {{/if}}

        <text x="90" text-anchor="middle" y="{{:((#getIndex()+1)*28)+12}}" alignment-baseline="middle" font-size="13" fill="#000000">{{:~dateFormat(start_date, ~getTimeFormat())}} - {{:~dateFormat(end_date, ~getTimeFormat())}}</text>
        {{/for}}
        {{/if}}
        */ ?>

        {{if teams.length}}
        {{for teams}}

        {{if (#getIndex()+1) < #get("root").data.teams.length}}
        <line x1="20" y1="{{:((#getIndex()+1)*28)+23}}" x2="{{if ~getTimeIntFormat()==24}}155{{else}}200{{/if}}" y2="{{:((#getIndex()+1)*28)+24}}" stroke="#cccccc78"/>
        {{/if}}

        <circle r="5" cx="25" cy="{{:((#getIndex()+1)*28)+10}}" fill="{{:team.team_color}}"/>
        <text x="47" class="cls-font" y="{{:(((#getIndex()+1)*28)+11)}}" text-anchor="middle" alignment-baseline="middle" font-size="12" fill="#000000">{{if team.team_leader.initials!=undefined}}{{:team.team_leader.initials}}{{else}}N/A{{/if}}</text>
        {{if #get("root").data.current_team_id == team.team_id}}
        <text transform="translate(-12 35)" stroke="#a57500" stroke-width="1" fill="#ffc400" x="44" y="{{:((#getIndex()+1)*28)-30}}" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="9" text-anchor="middle">&#9733;</text>
        {{/if}}

        <circle r="6" cx="{{if ~getTimeIntFormat()==24}}167{{else}}207{{/if}}" cy="{{:(((#getIndex()+1)*28)+11)}}" fill="#ffffff" stroke="red" stroke-width="1"/>
        <text x="{{if ~getTimeIntFormat()==24}}167{{else}}207{{/if}}" width="10" y="{{:(((#getIndex()+1)*28)+12)}}" text-anchor="middle" alignment-baseline="middle" font-size="8" class="font-red">{{:pointer}}</text>

        <g xmlns="http://www.w3.org/2000/svg" id="surface1" fill="{{:team.team_color}}" style="transform: translate(66px, {{:((#getIndex()+1)*28)+2}}px);">
            <path d="M 8.476562 2.429688 L 8.476562 1.265625 L 9.007812 1.265625 C 9.355469 1.265625 9.640625 0.984375 9.640625 0.632812 C 9.640625 0.285156 9.355469 0 9.007812 0 L 5.992188 0 C 5.644531 0 5.359375 0.285156 5.359375 0.632812 C 5.359375 0.984375 5.644531 1.265625 5.992188 1.265625 L 6.523438 1.265625 L 6.523438 2.429688 C 3.5 2.902344 1.179688 5.523438 1.179688 8.679688 C 1.179688 12.164062 4.015625 15 7.5 15 C 10.984375 15 13.820312 12.164062 13.820312 8.679688 C 13.820312 5.523438 11.5 2.902344 8.476562 2.429688 Z M 7.5 13.734375 C 4.710938 13.734375 2.445312 11.464844 2.445312 8.679688 C 2.445312 5.890625 4.710938 3.621094 7.5 3.621094 C 10.289062 3.621094 12.554688 5.890625 12.554688 8.679688 C 12.554688 11.464844 10.289062 13.734375 7.5 13.734375 Z M 7.5 13.734375 "/>
            <path d="M 8.132812 8.414062 L 8.132812 5.433594 C 8.132812 5.082031 7.847656 4.800781 7.5 4.800781 C 7.152344 4.800781 6.867188 5.082031 6.867188 5.433594 L 6.867188 8.78125 C 6.867188 8.882812 6.90625 8.980469 6.980469 9.054688 L 8.75 10.816406 C 9 11.0625 9.398438 11.0625 9.648438 10.816406 C 9.894531 10.566406 9.894531 10.167969 9.644531 9.921875 Z M 8.132812 8.414062 "/>
        </g>
        <text x="{{if ~getTimeIntFormat()==24}}118{{else}}140{{/if}}" text-anchor="middle" y="{{:((#getIndex()+1)*28)+12}}" alignment-baseline="middle" font-size="13" fill="#000000" class="cls-font">{{:~dateFormat(start_date, ~getTimeFormat())}} - {{:~dateFormat(end_date, ~getTimeFormat())}}</text>
        {{/for}}
        {{/if}}



    </g>
</svg>