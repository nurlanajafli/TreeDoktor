<!-- content -->
<section class="panel panel-default">
    <header class="panel-heading" style="display: flow-root;">
        <span class="pull-right">
            <a href="/settings/integrations/twilio/workspace/<?=$workspaceSid?>/workflow/create"
               class="btn btn-xs btn-primary create-workflow"><i class="fa fa-plus"></i></a>
        </span>
        Workflows
    </header>
    <table class="table table-striped m-b-none">
        <thead>
        <tr>
            <th style="min-width: 31px;">Workflow name</th>
            <th style="min-width: 31px;">Sid</th>
            <th style="min-width: 31px;">Task reservation timeout</th>
            <th style="min-width: 31px;">Assigment url callback</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($workflows)): ?>
            <?php foreach ($workflows as $workflow): ?>
                <tr>
                    <td><?= $workflow->friendlyName ?></td>
                    <td><?= $workflow->sid ?></td>
                    <td><?= $workflow->taskReservationTimeout ?></td>
                    <td><?= $workflow->assignmentCallbackUrl ?></td>
                    <td class="text-right">
                        <div class="btn-group">
                            <a href="/settings/integrations/twilio/workspace/<?= $workspaceSid ?>/workflow/update/<?= $workflow->sid; ?>"
                               class="edit action" title="Edit">
                                <i class="fa fa-pencil"></i>
                            </a>
                            <a href="/settings/integrations/twilio/workspace/<?= $workspaceSid ?>/workflow/delete/<?= $workflow->sid; ?>"
                               class="trash action delete-workflow" title="Delete">
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