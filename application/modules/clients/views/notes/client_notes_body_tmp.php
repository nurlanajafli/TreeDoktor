{{for client.notes.data }}
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
        {{if client_note}}
            <div class="m-b-sm">
                {{:client_note}}
            </div>
        {{/if}}

        {{if #get("root").data.notes_files
            && #get("root").data.notes_files[client_note_id] !== undefined
            && #get("root").data.notes_files[client_note_id].length
                ~clientId=client_id
                ~clientNoteId=client_note_id
        }}
            {{for #get("root").data.notes_files[client_note_id] itemVar="~file"}}
                <a target="_blank" href="{{:~clientNoteFile(~clientId, ~clientNoteId, ~file)}}">
                    <span class="label label-success">{{:~file}}</span>
                </a>
            {{/for}}
        {{/if}}
        {{if email && email_logs}}
            <div class="note-email-logs">
                <h6>Email log:</h6>
                {{:email_logs}}
            </div>
        {{/if}}
    </div>

    <div class="clear"></div>
    <div class="note-author border-top {{if robot != 'yes'}}alert-info{{else}}filled_dark_grey{{/if}}">
        <form data-type="ajax" data-location="<?php echo current_url(); ?>" data-url="/clients/ajax_top_note" class="inline pull-left">
            <div class="checkbox m-t-xs m-b-none m-l p-n" style="width: 65px;">
                <label class="checkbox-custom">
                    <input type="checkbox" {{if client_note_top}} checked="checked" {{/if}} name="note_top" value="1" onchange="$(this).parents('form:first').submit();">
                    <input type="hidden" name="note_id" value="{{:client_note_id}}">
                    <i class="fa fa-fw text-danger fa-square-o{{if client_note_top}} checked important-note{{/if}}"></i>
                    <span {{if client_note_top}} class="text-danger"{{/if}}>Important</span>
                </label>
            </div>
        </form>

        Created on:&nbsp;{{:client_note_date_view}}
        &nbsp;by&nbsp;{{if user}}{{:user.full_name}}{{/if}}
        <?php if (isAdmin()): ?>
            <a href="{{:~getAdminDeleteUrl(client_note_id)}}" class="btn btn-xs btn-danger delete-client-note">
                <i class="fa fa-trash-o"></i>
            </a>
        <?php endif; ?>
    </div>
</div>
{{/for}}

{{if client.notes.current_page < client.notes.last_page }}
<div class="text-center" id="load-more-notes">
    <form data-type="ajax" data-url="/clients/clientsNotes/notes" data-callback="ClientNotes.renderMore" data-global="false">
        <input type="hidden" name="lead_id" value="{{if lead!=undefined}}{{:lead.lead_id}}{{else}}0{{/if}}">
        <input type="hidden" name="client_id" value="{{:client.client_id}}">
        <input type="hidden" name="page" value="{{if client.notes.current_page != undefined}}{{:(client.notes.current_page+1)}}{{else}}2{{/if}}">
        <input type="hidden" name="client_note_type" value="{{if client_note_type!=undefined}}{{:client_note_type}}{{else}}all{{/if}}">
        <button type="submit" class="btn btn-block text-info bg-white" onclick="js:$(this.parentNode).trigger('submit')" data-toggle="class:show inline" data-target="#spin" data-loading-text="Loading...">Load More</button>
        <i class="fa fa-spin fa-spinner hide" id="spin"></i>
    </form>
</div>
{{/if}}