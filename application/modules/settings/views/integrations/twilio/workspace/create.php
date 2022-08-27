<?php
/** @var Twilio\Rest\Taskrouter\V1\WorkspaceInstance $workspace */
?>

<div id="workspaceCreateModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">

            <form name="modalWorkspaceForm" role="form" method="post" action="<?=base_url('/settings/integrations/twilio/workspace/create')?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Workspace name</label>
                        <input type="text" name="friendlyName" class="form-control" placeholder="Workspace name"
                               value="<?= $workspace->friendlyName ?? '' ?>"/>
                        <span class="form-attribute-invalid">
                        <?= (isset($data['errors']['friendlyName']) && !empty($data['errors']['friendlyName'])) ? $data['errors']['friendlyName'][0] : '' ?>
                    </span>
                    </div>
                    <div class="form-group">
                        <label>Event calback url <span style="color: darkgrey;"> http://example.com/callback/settings/task-callbacks</span></label>
                        <input type="text" name="eventCallbackUrl" class="form-control" placeholder="Url"
                               value="<?= $workspace->eventCallbackUrl ?? base_url('/callback/settings/task-callbacks') ?>"/>
                        <span class="form-attribute-invalid">
                        <?= (isset($data['errors']['eventCallbackUrl']) && !empty($data['errors']['eventCallbackUrl'])) ? $data['errors']['eventCallbackUrl'][0] : '' ?>
                        </span>
                        <p class="hidden form-attribute-invalid error alert-danger"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-default" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button type="submit" class="btn btn-sm btn-default pull-right">Submit</button>
                </div>
            </form>

        </div>
    </div>
</div>