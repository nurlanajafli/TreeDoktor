<?php
/** @var Twilio\Rest\Taskrouter\V1\Workspace\WorkflowInstance $workflow */
/** @var array $taskQueues */
$formAction = $creatAction
    ? "/settings/integrations/twilio/workspace/" . $workspaceSid . "/workflow/create"
    : "/settings/integrations/twilio/workspace/" . $workspaceSid . "/workflow/update/" . $workflow->sid;

?>
<form role="form" method="post" action="<?= $formAction ?>">
    <div class="col-sm-6">
        <div class="form-group">
            <label>Workflow name</label>
            <input type="text" name="friendlyName" class="form-control" placeholder="Workflow name"
                   value="<?= $workflow->friendlyName ?? '' ?>"/>
            <span class="form-attribute-invalid">
            <?= (isset($errors['friendlyName']) && !empty($errors['friendlyName'])) ? $errors['friendlyName'][0] : '' ?>
        </span>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label>Task reservetion timeout</label>

            <input type="number" name="taskReservationTimeout" class="form-control"
                   value="<?= $workflow->taskReservationTimeout ?? 120 ?>"/>
            <span class="form-attribute-invalid">
            <?= (isset($errors['taskReservationTimeout']) && !empty($errors['taskReservationTimeout'])) ? $errors['taskReservationTimeout'][0] : '' ?>
        </span>
        </div>
    </div>
    <hr>
    <div class="col-sm-6">
        <div class="form-group">
            <label>Workflow Assignment Callback</label>
            <input type="text" name="assignmentCallbackUrl" class="form-control" placeholder="Assigment url"
                   value="<?= $workflow->assignmentCallbackUrl ?? '' ?>"/>
            <span class="form-attribute-invalid">
            <?= (isset($errors['assignmentCallbackUrl']) && !empty($errors['assignmentCallbackUrl'])) ? $errors['assignmentCallbackUrl'][0] : '' ?>
        </span>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label>Fallback url</label>
            <input type="text" name="fallbackAssignmentCallbackUrl" class="form-control" placeholder="Fallback url"
                   value="<?= $workflow->fallbackAssignmentCallbackUrl ?? '' ?>"/>
            <span class="form-attribute-invalid">
            <?= (isset($errors['fallbackAssignmentCallbackUrl']) && !empty($errors['fallbackAssignmentCallbackUrl'])) ? $errors['fallbackAssignmentCallbackUrl'][0] : '' ?>
        </span>
        </div>
    </div>
    <hr>
    <div class="col-sm-12">
        <div class="task_routing panel panel-default">

            <?php if (isset($workflowConfiguration->task_routing->filters) && !empty($workflowConfiguration->task_routing->filters)): ?>
                <?php foreach ($workflowConfiguration->task_routing->filters as $key => $filter): ?>
                    <div class="tr_w">
                        <div>
                            <button class="task_routing-remove pull-right btn btn-default hide"><i
                                        class="fa fa-trash-o"></i></button>
                            <div class="task_routing_wrapper">
                                <div class="task_routing_wrapper_container">
                                    <div class="form-group">
                                        <label>Filter name</label>
                                        <input type="text"
                                               name="task_routing[filters][<?= $key ?>][filter_friendly_name]"
                                               class="form-control taskRoutingFilterFriendlyName" data-key='<?= $key ?>'
                                               placeholder="Unnamed filter"
                                               value="<?= $filter->filter_friendly_name ?? '' ?>"/>
                                    </div>
                                    <div class="form-group">
                                        <label>Matching tasks <?= $filter->expression ?></label>
                                        <input type="text" name="task_routing[filters][<?= $key ?>][expression]"
                                               class="form-control" placeholder="skills HAS 'support'"
                                               value="<?= $filter->expression ?>"/>
                                    </div>
                                    <?php foreach ($filter->targets as $targetKey => $target): ?>
                                        <div class="routing_steps">
                                            <div class="well">
                                                <h2>Routing Steps</h2>
                                                <button class="routing_steps-remove pull-right btn btn-default <?= $targetKey == 0 ? 'hide' : '' ?>">
                                                    <i class="fa fa-trash-o"></i></button>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <section class="panel panel-default">
                                                            <header class="panel-heading font-bold">Matching workers
                                                            </header>
                                                            <div class="panel-body">

                                                                <div class="form-group">
                                                                    <label>Task queue</label>
                                                                    <?php if (!empty($taskQueues)): ?>
                                                                        <select name="task_routing[filters][<?= $key ?>][targets][<?= $targetKey ?>][queue]"
                                                                                class="form-control task_routingFiltersTargetsQueue">
                                                                            <?php foreach ($taskQueues as $taskQueue): ?>
                                                                                <option value="<?= $taskQueue['sid'] ?>"
                                                                                        <?= $target->queue == $taskQueue['sid'] ? 'selected' : '' ?>>
                                                                                    <?= $taskQueue['friendlyName'] ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    <?php endif; ?>
                                                                </div>

                                                                <!--<div class="form-group">
                                                                    <label>Know worker</label>
                                                                    <div>
                                                                        <div class="col-md-4">
                                                                            <label>None</label>
                                                                            <input type="radio" name="filter[][optionsRadios][]" class="form-control"  />
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label>Worker friendly name</label>
                                                                            <input type="radio" name="filter[][optionsRadios][]" class="form-control"  />
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label>Worker SID</label>
                                                                            <input type="radio" name="filter[][optionsRadios][]" class="form-control"  />
                                                                        </div>
                                                                    </div>
                                                                </div>-->

                                                                <!-- <div class="form-group">
                                                            <label>Expression</label>
                                                            <input type="text" name="task_routing[filters][targets]['<?= $targetKey ?>'][expression]" class="form-control" placeholder="Any available worker in queue" value="<?= '' ?>"/>
                                                        </div> -->

                                                                <div class="form-group">
                                                                    <label>Ordering</label>
                                                                    <input type="text"
                                                                           name="task_routing[filters][<?= $key ?>][targets][<?= $targetKey ?>][order_by]"
                                                                           class="form-control" placeholder="ordering"
                                                                           value="<?= $target->order_by ?>"/>
                                                                </div>

                                                            </div>
                                                        </section>
                                                    </div>
                                                    <!--<div class="col-sm-6">
                                                <section class="panel panel-default">
                                                    <header class="panel-heading font-bold">Timeout & Priority</header>
                                                    <div class="panel-body">

                                                        <div class="form-group">
                                                            <label>Skip timeout</label>
                                                            <input type="text" name="task_routing[filters]['<?= $targetKey ?>'][skipIf]" class="form-control" placeholder="Do not skip" />
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Timeout</label>
                                                            <input type="text" name="task_routing[filters]['<?= $targetKey ?>'][timeout]" class="form-control" placeholder="None" />
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Priority</label>
                                                            <input type="text" name="task_routing[filters]['<?= $targetKey ?>'][priority]" class="form-control" placeholder="Inherit" />
                                                        </div>

                                                    </div>
                                                </section>
                                            </div>-->
                                                </div>
                                            </div>
                                        </div>
                                        <hr/>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <button class="add-step btn btn-primary">Add a Step</button>
                            <br/>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="tr_w">
                    <div>
                        <button class="task_routing-remove pull-right btn btn-default hide"><i
                                    class="fa fa-trash-o"></i></button>
                        <div class="task_routing_wrapper">
                            <div class="task_routing_wrapper_container">
                                <div class="form-group">
                                    <label>Filter name</label>
                                    <input type="text" name="task_routing[filters][0][filter_friendly_name]"
                                           data-key='0' class="form-control taskRoutingFilterFriendlyName"
                                           placeholder="Unnamed filter"/>
                                </div>
                                <div class="form-group">
                                    <label>Matching tasks</label>
                                    <input type="text" name="task_routing[filters][0][expression]" class="form-control"
                                           placeholder="skills HAS 'support'" value="Any Task"/>
                                </div>

                                <div class="routing_steps">
                                    <div class="well">
                                        <h3>Routing Steps</h3>
                                        <button class="routing_steps-remove pull-right btn btn-default hide"><i
                                                    class="fa fa-trash-o"></i></button>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <section class="panel panel-default">
                                                    <header class="panel-heading font-bold">Matching workers</header>
                                                    <div class="panel-body">

                                                        <div class="form-group">
                                                            <label>Task queue</label>
                                                            <?php if (!empty($taskQueues)): ?>
                                                                <select name="task_routing[filters][0][targets][0][queue]"
                                                                        class="form-control task_routingFiltersTargetsQueue">
                                                                    <?php foreach ($taskQueues as $taskQueue): ?>
                                                                        <option value="<?= $taskQueue['sid'] ?>">
                                                                            <?= $taskQueue['friendlyName'] ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!--<div class="form-group">
                                                            <label>Know worker</label>
                                                            <div>
                                                                <div class="col-md-4">
                                                                    <label>None</label>
                                                                    <input type="radio" name="filter[][optionsRadios][]" class="form-control"  />
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label>Worker friendly name</label>
                                                                    <input type="radio" name="filter[][optionsRadios][]" class="form-control"  />
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label>Worker SID</label>
                                                                    <input type="radio" name="filter[][optionsRadios][]" class="form-control"  />
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Expression</label>
                                                            <input type="text" name="filter[][expression]" class="form-control" placeholder="Any available worker in queue" />
                                                        </div>-->

                                                        <div class="form-group">
                                                            <label>Ordering</label>
                                                            <input type="text"
                                                                   name="task_routing[filters][0][targets][0][order_by]"
                                                                   class="form-control" placeholder="worker.level ASC"/>
                                                        </div>

                                                    </div>
                                                </section>
                                            </div>
                                            <!-- <div class="col-sm-6">
                                                 <section class="panel panel-default">
                                                     <header class="panel-heading font-bold">Timeout & Priority</header>
                                                     <div class="panel-body">

                                                         <div class="form-group">
                                                             <label>Skip timeout</label>
                                                             <input type="text" name="filter[][skipIf]" class="form-control" placeholder="Do not skip" />
                                                         </div>

                                                         <div class="form-group">
                                                             <label>Timeout</label>
                                                             <input type="text" name="filter[][timeout]" class="form-control" placeholder="None" />
                                                         </div>

                                                         <div class="form-group">
                                                             <label>Priority</label>
                                                             <input type="text" name="filter[][priority]" class="form-control" placeholder="Inherit" />
                                                         </div>

                                                     </div>
                                                 </section>
                                             </div>-->
                                        </div>
                                    </div>
                                </div>
                                <hr/>
                            </div>
                        </div>
                        <button class="add-step btn btn-primary">Add a Step</button>
                        <br/>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <button class="add-filter btn btn-default">Add a Filter +</button>
        </div>
    </div>

    <hr>
    <div class="col-sm-6">
        <div class="form-group">
            <label>Default task queue</label>
            <?php if (!empty($taskQueues)): ?>
                <select name="task_routing[default_filter][queue]" class="form-control">
                    <option value="0">None</option>
                    <?php foreach ($taskQueues as $taskQueue): ?>
                        <option value="<?= $taskQueue['sid'] ?>"
                                <?= isset($workflowConfiguration->task_routing->default_filter->queue) && $workflowConfiguration->task_routing->default_filter->queue == $taskQueue['sid'] ? 'selected' : '' ?>>
                            <?= $taskQueue['friendlyName'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>
        <span class="form-attribute-invalid">
        <?= (isset($errors['default_filter']) && !empty($errors['default_filter'])) ? $errors['default_filter'][0] : '' ?>
        </span>
    </div>
    <div class="col-sm-12">
        <a href="/settings/integrations/twilio/workspace/<?=$workspaceSid?>/workflow" class="btn btn-sm btn-default pull-left">Back</a>
        <button type="submit" class="btn btn-sm btn-success pull-right">Submit</button>
    </div>
</form>

