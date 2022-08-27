<?php

use application\modules\settings\integrations\twilio\classes\task_router\TaskQueueTwilio;
use application\modules\settings\integrations\twilio\classes\task_router\WorkflowTwilio;
use application\modules\settings\integrations\twilio\libraries\AppletInstance;
use application\modules\settings\integrations\twilio\libraries\AppletUI;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkspaceModel;

$say = (array)AppletInstance::getValue('say[]', []);
$play_before_task_action = AppletInstance::getValue('play_before_task_action', 'off');
$play_music_while_calling_action = AppletInstance::getValue('play_music_while_calling_action', 'off');
$no_answer_action = AppletInstance::getValue('no-answer-action', 'voicemail');
$workspace_sid = AppletInstance::getValue("workspace_sid", null);
$workspaces = SoftTwilioWorkspaceModel::getList();
$taskQueues = (is_null($workspace_sid) || empty($workspace_sid)) ? [] : (new TaskQueueTwilio($workspace_sid))->getListAllTaskQueues();
$workflows = (is_null($workspace_sid) || empty($workspace_sid)) ? [] : (new WorkflowTwilio($workspace_sid))->getList();

?>
<div class="vbx-applet" id="queueApplet">
    <h2>Add Call to queue</h2>
    <p>Put this call into the hold queue uniquely identified by the string below</p>

    <div class="vbx-applet dial-applet">
        <h3>Before Calling</h3>
        <div class="radio-table">
            <table>
                <tr class="radio-table-row first <?= ($play_before_task_action === 'on') ? 'on' : 'off' ?>">
                    <td class="radio-cell">
                        <input type="radio" class='dial-whom-selector-radio' name="play_before_task_action"
                               value="on" <?= ($play_before_task_action === 'on') ? 'checked="checked"' : '' ?>/>
                    </td>
                    <td class="content-cell">
                        <?= AppletUI::audioSpeechPicker('play_before_task', false); ?>
                    </td>
                </tr>
                <tr class="radio-table-row last <?= ($play_before_task_action === 'off') ? 'on' : 'off' ?>">
                    <td class="radio-cell">
                        <input type="radio" class='dial-whom-selector-radio' name="play_before_task_action"
                               value="off" <?= ($play_before_task_action === 'off') ? 'checked="checked"' : '' ?>/>
                    </td>
                    <td class="content-cell">
                        <h4>Nothing</h4>
                    </td>
                </tr>
            </table>
        </div>
        <hr/>
    </div>

    <div class="col-md-12">
        <h3>If wrap inside Gather, please set a numDigits</h3>

        <section class="panel panel-default">
            <header class="panel-heading">
                <div class="input-group text-sm">
                    <fieldset class="vbx-input-container">
                        <input class="keypress small play_music_while_calling_gather" type="text" name="gather"
                               value="<?= AppletInstance::getValue("gather") ?>"/>
                    </fieldset>
                </div>
            </header>
        </section>

    </div>

    <div class="vbx-applet dial-applet music_block">
        <?php if (!empty($say)): ?>
            <?php foreach ($say as $i => $key): ?>
                <div class="music_block_wrapper" data-key="<?= $i ?>">
                    <div class="pull-right">
                        <a href="" class="add action fa fa-plus-circle <?= $i != 0 ? 'hidden' : '' ?>">
                            <span class="replace">Add</span>
                        </a>
                        <a href="" class="remove action fa fa-minus-circle <?= $i != 0 ? '' : 'hidden' ?>">
                            <span class="replace">Remove</span>
                        </a>
                    </div>
                    <h3>Music While Calling</h3>
                    <div class="radio-table">
                        <table>
                            <tr class="radio-table-row first">
                                <td class="content-cell">
                                    <?= AppletUI::audioSpeechPicker('music[' . $i . ']', false); ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="text-table">
                        <table>
                            <tr class="input-table-row">
                                <td>
                                    <h4>How many times repeat audio</h4>
                                    <fieldset class="vbx-input-container">
                                        <input class="keypress medium play_music_while_calling_repeat" type="text"
                                               name="repeat[<?= $i ?>]"
                                               value="<?= AppletInstance::getValue('repeat[' . $i . ']') ?>"/>
                                    </fieldset>
                                </td>
                            </tr>
                            <tr class="input-table-row">
                                <td>
                                    <h4>Say between audio</h4>
                                    <fieldset class="vbx-input-container">
                                        <input class="keypress medium play_music_while_calling_say" type="text"
                                               name="say[<?= $i ?>]" value="<?= $key ?>"/>
                                    </fieldset>
                                </td>
                            </tr>
                            <tr class="input-table-row">
                                <td>
                                    <h4>Pause in seconds</h4>
                                    <fieldset class="vbx-input-container">
                                        <input class="keypress medium play_music_while_calling_pause" type="text"
                                               name="pause[<?= $i ?>]"
                                               value="<?= AppletInstance::getValue('pause[' . $i . ']') ?>"/>
                                    </fieldset>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <hr/>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="music_block_wrapper" data-key="0">
                <div class="pull-right">
                    <a href="" class="add action fa fa-plus-circle">
                        <span class="replace">Add</span>
                    </a>
                    <a href="" class="remove action fa fa-minus-circle hidden">
                        <span class="replace">Remove</span>
                    </a>
                </div>
                <h3>Music While Calling</h3>
                <div class="radio-table">
                    <table>
                        <tr class="radio-table-row first">
                            <td class="content-cell">
                                <?= AppletUI::audioSpeechPicker('music[0]', false); ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="text-table">
                    <table>
                        <tr class="input-table-row">
                            <td>
                                <h4>How many times repeat audio</h4>
                                <fieldset class="vbx-input-container">
                                    <input class="keypress medium play_music_while_calling_repeat" type="text"
                                           name="repeat[0]" value="1"/>
                                </fieldset>
                            </td>
                        </tr>
                        <tr class="input-table-row">
                            <td>
                                <h4>Say between audio</h4>
                                <fieldset class="vbx-input-container">
                                    <input class="keypress medium play_music_while_calling_say" type="text"
                                           name="say[0]" value=""/>
                                </fieldset>
                            </td>
                        </tr>
                        <tr class="input-table-row">
                            <td>
                                <h4>Pause in seconds</h4>
                                <fieldset class="vbx-input-container">
                                    <input class="keypress medium play_music_while_calling_pause" type="text"
                                           name="pause[0]" value="1"/>
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                </div>
                <hr/>
            </div>
        <?php endif; ?>
    </div>

    <div class="coll-md-12">
        <h3>Choose Twilio Task</h3>
        <section class="panel panel-default queueInTaskQueuesList">
            <header class="panel-heading">

                <div class="input-group text-sm">
                    <input type="hidden" name="workspace_sid" value="<?= $workspace_sid ?>"/>
                    <div class="pull-left">
                        <label>Choose workspace</label>
                        <?php if (!empty($workspaces)): ?>
                            <select name="workspace_sid_select" class="form-control workspaceSelect">
                                <option value="0">Set workspace</option>
                                <?php foreach ($workspaces as $workspace): ?>
                                    <option value="<?= $workspace['sid'] ?>"
                                            <?= $workspace_sid == $workspace['sid'] ? 'selected' : '' ?>>
                                        <?= $workspace['friendlyName'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>

                <hr>

                <div class="input-group text-sm">
                    <div class="pull-left">
                        <label>Choose Workflow</label>
                        <select name="workflow_sid" class="form-control">
                            <option value="0">Set workflow</option>
                            <?php if (!empty($workflows)): ?>
                                <?php foreach ($workflows as $workflow): ?>
                                    <option value="<?= $workflow['sid'] ?>"
                                            <?= AppletInstance::getValue("workflow_sid") == $workflow['sid'] ? 'selected' : '' ?>>
                                        <?= $workflow['friendlyName'] ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <hr>

                <!--<div class="input-group text-sm">
                        <input type="hidden" name="queue_name" value="<?= AppletInstance::getValue("queue_name") ?>"/>
                        <div class="pull-left">
                            <label>Choose Task Queue</label>
                            <select name="task_queue_sid" class="form-control">
                                <option value="0">Set task queue</option>
                                <?php if (!empty($taskQueues)): ?>
                                    <?php foreach ($taskQueues as $taskQueue): ?>
                                        <option value="<?= $taskQueue['sid'] ?>"
                                                <?= AppletInstance::getValue("task_queue_sid") == $taskQueue['sid'] ? 'selected' : '' ?>>
                                            <?= $taskQueue['friendlyName'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="input-group-btn open pull-right <?= is_null($workspace_sid) ? 'hidden' : '' ?> create_taskQueue_for_workspace">
                            <button type="button" id="bottonCreateTaskQueue" class="btn btn-xs btn-success">Create task
                                queue
                            </button>
                        </div>
                    </div>-->
            </header>
            <!--<ul class="list-group" id="taskQueueList">
                    <?php if (!empty($taskQueues)): ?>
                        <?php foreach ($taskQueues as $taskQueue): ?>
                            <li class="list-group-item list-group-sid-<?= $taskQueue['sid'] ?>">
                                <div class="media-body">
                                    <div class="pull-right">
                                        <button class="taskQueueEdit btn btn-xs btn-default"
                                                data-sid="<?= $taskQueue['sid'] ?>">Edit
                                        </button>
                                        <button class="taskQueueDelete btn btn-xs btn-danger"
                                                data-sid="<?= $taskQueue['sid'] ?>">Delete
                                        </button>
                                    </div>
                                    <span class="pull-left"><?= $taskQueue['friendlyName'] ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>-->
        </section>

    </div>

    <div class="vbx-applet dial-applet">
        <h3>If nobody answers...</h3>
        <div class="radio-table no-answer nobody-answers-user-group">
            <table>
                <tr class="voicemail-row radio-table-row first">
                    <td class="radio-cell">
                        <input type="radio" class='no-answer-action-radio' name="no-answer-action" value="voicemail"
                               checked="checked"/>
                    </td>
                    <td class="content-cell" style="vertical-align: middle;">
                        <div class="personal-voicemail">
                            <h4>Take a voicemail</h4>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</div>
