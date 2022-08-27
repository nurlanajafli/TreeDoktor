<script id="client_locations_filter_tpl" type="text/x-jsrender">
    <blockquote style="padding: 0px; margin: 7px 5px; font-size: 12px;">
        <div class="dropdown m-l">
            <span class="dropdown-toggle th-sortable" data-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-map-marker m-r-xs"></i>
                <span id="location-selected-label">{{if data.length > 1}}Any Address{{else}}{{:data[0].location}}{{/if}}</span>
                {{if data.length > 1}}
                    <b class="caret"></b>
                {{/if}}
            </span>
            {{if data.length > 1}}
                <ul class="dropdown-menu text-left animated fadeInLeft" id="select-location-dropdown">
                    <li class="active"><a href="#dropdown-0" data-address="" data-toggle="tab" class="change-location">Any Address</a></li>
                    {{for data}}
                        <li>
                            <a href="#dropdown-{{:(#getIndex()+1).toString()}}" data-address="{{:lead_address}}" data-toggle="tab" class="change-location">
                                {{:location}}
                            </a>
                        </li>
                    {{/for}}
                </ul>
            {{/if}}
        </div>
    </blockquote>
</script>
