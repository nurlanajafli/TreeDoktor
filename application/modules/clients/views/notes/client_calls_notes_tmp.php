<div class="tab-pane active animated fadeInUp" id="{{if client_note_type!=undefined}}{{:client_note_type}}{{else}}all{{/if}}" style="overflow-x: hidden">

            {{if notes!=undefined && ~object_length(notes)}}
            {{for notes}}
                <div class="media m-t-sm">

                    <div class="pull-left m-l">
                        <span class="thumb-md">
                            {{if user}}
                            <img src="{{:user.picture}}" class="img-circle">
                            {{else}}
                            <img src="{{:~getUserAvatar()}}" class="img-circle">
                            {{/if}}
                        </span>
                    </div>
                    <div class="h6 media-body p-10 client-note">
                        <div class="m-b-sm">
                            <div class="p-10">
                                <div class="row">
                                    <div class="col-md-1 call-route h3 m-t-3">
                                        <i class="fa fa-sign-{{if call_route}}in{{else}}out{{/if}} text-{{if call_route}}{{if call_duration}}success{{else}}danger{{/if}}{{else}}info{{/if}}"></i>
                                    </div>
                                    <div class="col-md-6 call-info">
                                        <div class="row">
                                            <div class="col-sm-4"><strong>From:</strong></div>
                                            <div class="col-sm-8">
                                                <span class="call-agent-name">
                                                    {{if call_route || (call_route && !twilio_worker_id) }}
                                                        {{:call_from}}
                                                    {{else !call_route && twilio_worker_id}}
                                                        {{user.full_name}}
                                                    {{/if}}
                                                </span>
                                            </div>
                                        </div>
                                        <br>
                                        <div class="row">
                                            <div class="col-sm-4"><strong>Client:</strong></div>
                                            <div class="col-sm-8">
                                                {{if client}}
                                                <a class="text-ul client-iframe" href="{{client.client_id}}"><strong>{{: client.client_name}}</strong></a>
                                                {{/if}}
                                            </div>
                                        </div>
                                        <br>
                                        <div class="row">
                                            <div class="col-sm-4"><strong>To:</strong></div>
                                            <div class="col-sm-8">
                                                {{if !call_route || (call_route && !twilio_worker_id)}}
                                                    {{:call_to}}
                                                {{else call_route && twilio_worker_id}}
                                                    {{:user.full_name}}
                                                {{/if}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5 text-center">
                                        {{if call_voice}}
                                            <div class="ui360 m-t-3">
                                                <a href="{{:call_voice}}.mp3" data-title="Play Recording"></a>
                                            </div>
                                        {{/if}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="note-author border-top filled_dark_grey">
                        Created on:&nbsp;{{:call_date_view}}
                    </div>
                </div>


            {{/for}}
            {{if notes!=undefined && notes.length > limit }}
            <div class="text-center">
                <a href="#" class="getMore" data-num="{{:limit}}" data-type="{{if client_note_type!=undefined}}{{:client_note_type}}{{/if}}" data-id="{{:client.client_id}}">Show More</a>
            </div>
            {{/if}}
            {{else}}
            <div class="client_note filled_white rounded shadow overflow">
                <div class="corner"></div>
                <div class="p-20 h5 text-center">
                    No record found
                </div>
            </div>
            {{/if}}

</div>