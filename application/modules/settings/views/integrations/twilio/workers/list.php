<!-- content -->
<section class="panel panel-default">
    <header class="panel-heading" style="display: flow-root;">
        <span class="pull-right">
            <a href="/settings/integrations/twilio/workspace/<?=$workspaceSid?>/worker/create"
               class="btn btn-xs btn-primary create-workflow createNewWorker"
               data-available_users="<?=empty($availableUsers) ? 0 : 1?>"><i class="fa fa-plus"></i></a>
        </span>
        Workers
    </header>
    <table class="table table-striped m-b-none">
        <thead>
        <tr>
            <th style="min-width: 31px;">Worker name</th>
            <th style="min-width: 31px;">Activity</th>
            <th style="min-width: 31px;">Available</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($workers)): ?>
            <?php foreach ($workers as $worker): ?>
                <tr>
                    <td><?= $worker->friendlyName ?></td>
                    <td><?= $worker->activityName?></td>
                    <td><?= $worker->available ? 'true' : 'false' ?></td>
                    <td class="text-right">
                        <div class="btn-group">
                            <a href="/settings/integrations/twilio/workspace/<?=$workspaceSid?>/worker/update/<?= $worker->sid; ?>"
                               class="edit action" title="Edit">
                                <i class="fa fa-pencil"></i>
                            </a>
                            <a href="/settings/integrations/twilio/workspace/<?= $workspaceSid ?>/worker/delete/<?= $worker->sid; ?>"
                               class="trash action delete-worker" title="Delete">
                                <i class="text-danger fa fa-trash-o"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center;">No data available</p>
        <?php endif; ?>
        </tbody>
    </table>
</section>
<!-- //content -->