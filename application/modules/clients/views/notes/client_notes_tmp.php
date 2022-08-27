
<section class="panel panel-default p-n notoverflowed">
    <div id="client-notes-tabs" style="padding: 15px 15px 0 15px;">
        <ul class="nav nav-tabs">
            <li {{if !client_note_type && (client_only==false || '<?php echo $this->router->fetch_class() ?>' == 'clients')}}class="active"{{/if}}>
                <a href="#all"
                   class="client-notes-tab"
                   data-client_note_type=""
                   data-client_only="false"
                   data-lead_id="{{if lead!=undefined}}{{:lead.lead_id}}{{else}}0{{/if}}"
                   data-client_id="{{if client }}{{:client.client_id}}{{/if}}">All Notes</a>
            </li>
            <li {{if client_note_type!=undefined && client_note_type=="info"}}class="active"{{/if}}>
                <a href="#info"
                   class="client-notes-tab"
                   data-client_note_type="info"
                   data-lead_id="{{if lead!=undefined}}{{:lead.lead_id}}{{else}}0{{/if}}"
                   data-client_id="{{if client }}{{:client.client_id}}{{/if}}">Info</a>
            </li>
            <li {{if client_note_type!=undefined && client_note_type=="attachment"}}class="active"{{/if}}>
                <a href="#attachment"
                   class="client-notes-tab"
                   data-client_note_type="attachment"
                   data-lead_id="{{if lead!=undefined}}{{:lead.lead_id}}{{else}}0{{/if}}"
                   data-client_id="{{if client }}{{:client.client_id}}{{/if}}">Attachment</a>
            </li>
            <li {{if client_note_type!=undefined && client_note_type=="system"}}class="active"{{/if}}>
                <a href="#system"
                   class="client-notes-tab"
                   data-client_note_type="system"
                   data-lead_id="{{if lead!=undefined}}{{:lead.lead_id}}{{else}}0{{/if}}"
                   data-client_id="{{if client }}{{:client.client_id}}{{/if}}">System</a>
            </li>
            <?php if(config_item('phone')) : ?>
                <li {{if client_note_type!=undefined && client_note_type=="calls"}}class="active"{{/if}}>
                    <a href="#calls"
                       class="client-call-notes-tab"
                       data-client_note_type="calls"
                       data-lead_id="{{if lead!=undefined}}{{:lead.lead_id}}{{else}}0{{/if}}"
                       data-client_id="{{if client }}{{:client.client_id}}{{/if}}">Calls</a>
                </li>
            <?php endif; ?>
            <?php if(config_item('messenger')) : ?>
                <li {{if client_note_type!=undefined && client_note_type=="sms"}}class="active"{{/if}}>
                    <a href="#sms"
                       class="client-sms-notes-tab"
                       data-client_note_type="sms"
                       data-lead_id="{{if lead!=undefined}}{{:lead.lead_id}}{{else}}0{{/if}}"
                       data-client_id="{{if client }}{{:client.client_id}}{{/if}}">Sms</a>
                </li>
            <?php endif; ?>
            <li {{if client_note_type!=undefined && client_note_type=="email"}}class="active"{{/if}}>
                <a href="#email"
                   class="client-notes-tab"
                   data-client_note_type="email"
                   data-lead_id="{{if lead!=undefined}}{{:lead.lead_id}}{{else}}0{{/if}}"
                   data-client_id="{{if client }}{{:client.client_id}}{{/if}}">Email</a>
            </li>
            <?php if ($this->router->fetch_class() != 'clients') : ?>
                <li {{if !client_note_type && client_only==true}}class="active"{{/if}}>
                    <a href="#all"
                       class="client-notes-tab"
                       data-client_note_type=""
                       data-client_only="true"
                       data-client_id="{{if client }}{{:client.client_id}}{{/if}}"
                       data-lead_id="{{if lead!=undefined}}{{:lead.lead_id}}{{else}}0{{/if}}">All Client Notes</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="tab-content" id="client-notes-tab-content">
        <div class="tab-pane active animated fadeInUp" id="{{if client_note_type!=undefined && client_note_type}}{{:client_note_type}}{{else}}all{{/if}}" style="overflow-x: hidden">
            {{if client.notes.data!=undefined && client.notes.data.length}}
                {{include tmpl="#client-notes-body-tmp" /}}
            {{else}}
            <div class="client_note filled_white rounded shadow overflow">
                <div class="corner"></div>
                <div class="p-20 h5 text-center">
                    No record found
                </div>
            </div>
            {{/if}}
        </div>
    </div>
</section>
