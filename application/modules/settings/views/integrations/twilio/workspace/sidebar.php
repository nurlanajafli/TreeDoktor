<!-- .aside -->
<aside class="aside-bg bg-white b-r" id="subNav">
    <ul class="nav workspaceSidebar">
        <li class="b-b b-light">
            <a href="/settings/integrations/twilio/workspace/overview/<?= $workspaceSid ?>"
               class="<?= strpos(current_url(), 'overview') ? 'active' : '' ?>">
                <i class="fa fa-chevron-right pull-right m-t-xs text-xs icon-muted"></i>Overview
            </a>
        </li>
        <li class="b-b b-light">
            <a href="/settings/integrations/twilio/workspace/<?= $workspaceSid ?>/task-queue"
               class="<?= strpos(current_url(), 'task-queue') ? 'active' : '' ?>">
                <i class="fa fa-chevron-right pull-right m-t-xs text-xs icon-muted"></i>TaskQueue
            </a>
        </li>
        <li class="b-b b-light">
            <a href="/settings/integrations/twilio/workspace/<?= $workspaceSid ?>/workflow"
               class="<?= strpos(current_url(), 'workflow') ? 'active' : '' ?>">
                <i class="fa fa-chevron-right pull-right m-t-xs text-xs icon-muted"></i>Workflows
            </a>
        </li>
        <li class="b-b b-light">
            <a href="/settings/integrations/twilio/workspace/<?= $workspaceSid ?>/activity"
               class="<?= strpos(current_url(), 'activity') ? 'active' : '' ?>">
                <i class="fa fa-chevron-right pull-right m-t-xs text-xs icon-muted"></i>Activities
            </a>
        </li>
        <li class="b-b b-light">
            <a href="/settings/integrations/twilio/workspace/<?= $workspaceSid ?>/worker"
               class="<?= strpos(current_url(), 'worker') ? 'active' : '' ?>">
                <i class="fa fa-chevron-right pull-right m-t-xs text-xs icon-muted"></i>Workers
            </a>
        </li>
        <li class="b-b b-light">
            <a href="/settings/integrations/twilio">
                <button type="submit" class="btn btn-sm btn-default center-block workspaceSideBarBackButton">
                    Back<i class="fa fa-chevron-left pull-left m-t-xs text-xs icon-muted"></i>
                </button>
            </a>
        </li>
    </ul>
</aside>
<!-- /.aside -->