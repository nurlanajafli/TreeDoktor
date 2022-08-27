<!-- content -->
<section class="panel panel-default">
    <header class="panel-heading" style="display: flow-root;">
        <span class="pull-right">
            <a href="/settings/integrations/twilio/workspace/<?=$workspaceSid?>/activity/create"
               class="btn btn-xs btn-primary create-workflow"><i class="fa fa-plus"></i></a>
        </span>
        Activities
    </header>
    <table class="table table-striped m-b-none">
        <thead>
        <tr>
            <th style="min-width: 31px;">Activity name</th>
            <th style="min-width: 31px;">Sid</th>
            <th style="min-width: 31px;">Availability</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($activities)): ?>
            <?php foreach ($activities as $activity): ?>
                <tr>
                    <td><?= $activity->friendlyName ?></td>
                    <td><?= $activity->sid ?></td>
                    <td><?= $activity->available ? 'true' : 'false' ?></td>
                    <td class="text-right">
                        <div class="btn-group">
                            <a href="/settings/integrations/twilio/workspace/<?=$workspaceSid?>/activity/update/<?= $activity->sid; ?>"
                               class="edit action" title="Edit">
                                <i class="fa fa-pencil"></i>
                            </a>
                            <a href="/settings/integrations/twilio/workspace/<?= $workspaceSid ?>/activity/delete/<?= $activity->sid; ?>"
                               class="trash action delete-activity" title="Delete">
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