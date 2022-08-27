<!-- content -->

<section class="panel panel-default">
    <header class="panel-heading" style="display: flow-root;">
        <span class="pull-right">
            <a href="/settings/integrations/twilio/workspace/<?= $workspaceSid ?>/task-queue/create"
               class="btn btn-xs btn-primary create-workspace"><i class="fa fa-plus"></i></a>
        </span>
        Task Queues
    </header>
    <table class="table table-striped m-b-none">
        <thead>
        <tr>
            <th style="min-width: 31px;">TaskQueue name</th>
            <th style="min-width: 31px;">Sid</th>
            <th style="min-width: 31px;">Target Worker Expression</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($taskQueues)): ?>
            <?php foreach ($taskQueues as $taskQueue): ?>
                <tr>
                    <td><?= $taskQueue->friendlyName ?></td>
                    <td><?= $taskQueue->sid ?></td>
                    <td><?= $taskQueue->targetWorkers ?></td>
                    <td class="text-right">
                        <div class="btn-group">
                            <a href="/settings/integrations/twilio/workspace/<?= $workspaceSid ?>/task-queue/update/<?= $taskQueue->sid; ?>"
                               class="edit action" title="Edit">
                                <i class="fa fa-pencil"></i>
                            </a>
                            <a href="/settings/integrations/twilio/workspace/<?= $workspaceSid ?>/task-queue/delete/<?= $taskQueue->sid; ?>"
                               class="trash action delete-task-queue" title="Delete">
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