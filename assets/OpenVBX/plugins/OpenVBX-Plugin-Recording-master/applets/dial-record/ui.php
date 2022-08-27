<?php

use application\modules\settings\integrations\twilio\libraries\AppletInstance;
use application\modules\settings\integrations\twilio\libraries\AppletUI;
use application\modules\settings\integrations\twilio\libraries\AppletUI\Exceptions\VBX_IncomingNumberException;
use application\modules\settings\models\integrations\twilio\VBXIncomingNumbers;

$numbers = (new VBXIncomingNumbers())->get_numbers();

$callerId = AppletInstance::getValue('callerId', null);
$version = AppletInstance::getValue('version', null);

if (AppletInstance::getValue('dial-whom-selector', 'user-or-group') === 'user-or-group') {
    $showVoicemailAction = true;
} else {
    $showVoicemailAction = false;
}

$userOrGroup = AppletInstance::getUserGroupPickerValue('dial-whom-user-or-group');
$showGroupVoicemailPrompt = false;

$dial_whom_selector = AppletInstance::getValue('dial-whom-selector', 'user-or-group');
$recording_enable = AppletInstance::getValue('recording-enable', 'no');
$no_answer_action = AppletInstance::getValue('no-answer-action', 'voicemail');
$whisper = AppletInstance::getValue('dial-whisper', true);
$say_before_dial_action = AppletInstance::getValue('say-before-dial-action', 'off');
$dial_timeout = AppletInstance::getValue('dial_timeout') ?? 10;
?>
<div class="vbx-applet dial-applet">

    <h2>Dial Whom</h2>
    <div class="radio-table">
        <table>
            <tr class="radio-table-row first <?= ($dial_whom_selector === 'user-or-group') ? 'on' : 'off' ?>">
                <td class="radio-cell">
                    <input type="radio" class='dial-whom-selector-radio' name="dial-whom-selector"
                           value="user-or-group"
                           <?= ($dial_whom_selector === 'user-or-group') ? 'checked="checked"' : '' ?>/>
                </td>
                <td class="content-cell">
                    <h4>Dial a user or group</h4>
                    <?= AppletUI::UserGroupPicker('dial-whom-user-or-group'); ?>
                </td>
            </tr>
            <tr class="radio-table-row last <?= ($dial_whom_selector === 'number') ? 'on' : 'off' ?>">
                <td class="radio-cell">
                    <input type="radio" class='dial-whom-selector-radio' name="dial-whom-selector"
                           value="number" <?= ($dial_whom_selector === 'number') ? 'checked="checked"' : '' ?>/>
                </td>
                <td class="content-cell">
                    <h4>Dial phone number</h4>
                    <div class="vbx-input-container input">
                        <input type="text" class="medium" name="dial-whom-number"
                               value="<?= AppletInstance::getValue('dial-whom-number') ?>"/>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <br/>
    <h2>Caller ID</h2>
    <div class="vbx-full-pane">
        <fieldset class="vbx-input-container">
            <select class="medium" name="callerId">
                <option value="">Caller's Number</option>
                <?php if (count($numbers)) {
                    foreach ($numbers as $number):?>
                        <option value="<?= $number; ?>"
                                <?= $number == $callerId ? ' selected="selected" ' : ''; ?>><?= $number; ?></option>
                    <?php endforeach;
                } ?>
            </select>
        </fieldset>
    </div>
    <br/>

    <h2>Timeout in seconds</h2>
    <div class="vbx-full-pane">
        <fieldset class="vbx-input-container">
            <input type="text" class="medium" name="dial_timeout"
                   value="<?= empty($dial_timeout) ? 10 : $dial_timeout ?>"/>
        </fieldset>
    </div>
    <br/>

    <h2>Say Before Dial</h2>
    <div class="radio-table">
        <table>
            <tr class="radio-table-row first <?= ($say_before_dial_action === 'on') ? 'on' : 'off' ?>">
                <td class="radio-cell">
                    <input type="radio" class='dial-whom-selector-radio' name="say-before-dial-action"
                           value="on" <?= ($say_before_dial_action === 'on') ? 'checked="checked"' : '' ?>/>
                </td>
                <td class="content-cell">
                    <?= AppletUI::AudioSpeechPicker('say-before-dial'); ?>
                </td>
            </tr>
            <tr class="radio-table-row last <?= ($say_before_dial_action === 'off') ? 'on' : 'off' ?>">
                <td class="radio-cell">
                    <input type="radio" class='dial-whom-selector-radio' name="say-before-dial-action"
                           value="off" <?= ($say_before_dial_action === 'off') ? 'checked="checked"' : '' ?>/>
                </td>
                <td class="content-cell">
                    <h4>Nothing</h4>
                </td>
            </tr>
        </table>
    </div>
    <br/>

    <h2>Call Recording</h2>
    <div class="radio-table">
        <table>
            <tr class="radio-table-row first <?= ($recording_enable === 'record-from-answer') ? 'on' : 'off' ?>">
                <td class="radio-cell">
                    <input type="radio" class='dial-whom-selector-radio' name="recording-enable"
                           value="record-from-answer"
                           <?= ($recording_enable === 'record-from-answer') ? 'checked="checked"' : '' ?>/>
                </td>
                <td class="content-cell">
                    <h4>Record From Answer</h4>
                </td>
            </tr>
            <tr class="radio-table-row <?= ($recording_enable === 'record-from-ringing') ? 'on' : 'off' ?>">
                <td class="radio-cell">
                    <input type="radio" class='dial-whom-selector-radio' name="recording-enable"
                           value="record-from-ringing"
                           <?= ($recording_enable === 'record-from-ringing') ? 'checked="checked"' : '' ?>/>
                </td>
                <td class="content-cell">
                    <h4>Record From Ringing</h4>
                </td>
            </tr>
            <tr class="radio-table-row last <?= ($recording_enable === 'no') ? 'on' : 'off' ?>">
                <td class="radio-cell">
                    <input type="radio" class='dial-whom-selector-radio' name="recording-enable"
                           value="no" <?= ($recording_enable === 'no') ? 'checked="checked"' : '' ?>/>
                </td>
                <td class="content-cell">
                    <h4>Disable</h4>
                </td>
            </tr>
        </table>
    </div>
    <br/>

    <!--<h2>Whisper</h2>
    <div class="radio-table">
        <table>
            <tr class="radio-table-row first <?= ($whisper) ? 'on' : 'off' ?>">
                <td class="radio-cell">
                    <input type="radio" class='dial-whisper-radio' name="dial-whisper"
                           value="0" <?= ($whisper) ? 'checked="checked"' : '' ?>/>
                </td>
                <td class="content-cell">
                    <h4>Announce the caller</h4>
                </td>
            </tr>
            <tr class="radio-table-row last <?= (!$whisper) ? 'on' : 'off' ?>">
                <td class="radio-cell">
                    <input type="radio" class='dial-whisper-radio' name="dial-whisper"
                           value="1" <?= (!$whisper) ? 'checked="checked"' : '' ?>/>
                </td>
                <td class="content-cell">
                    <h4>Connect without announcing</h4>
                </td>
            </tr>
        </table>
    </div>
    <br/>-->

    <h2>If nobody answers...</h2>
    <div class="radio-table no-answer nobody-answers-user-group <?= ($dial_whom_selector === 'user-or-group') ? '' : 'hide' ?>">
        <table>
            <tr class="voicemail-row radio-table-row first <?= ($no_answer_action === 'voicemail') ? 'on' : 'off' ?> <?= $showVoicemailAction ? '' : 'hide' ?>">
                <td class="radio-cell">
                    <input type="radio" class='no-answer-action-radio' name="no-answer-action"
                           value="voicemail" <?= ($no_answer_action === 'voicemail') ? 'checked="checked"' : '' ?>/>
                </td>
                <td class="content-cell" style="vertical-align: middle;">
                    <div class="personal-voicemail <?= $showGroupVoicemailPrompt ? 'hide' : '' ?>">
                        <h4>Take a voicemail</h4>
                    </div>
                    <div class="group-voicemail <?= $showGroupVoicemailPrompt ? '' : 'hide' ?>">
                        <table>
                            <tr style="border-bottom-width: 0px;">
                                <td align="left" style="vertical-align: middle;"><h4>Take a voicemail</h4></td>
                                <td>&nbsp;&nbsp;&nbsp;</td>
                                <td style="width: 100%; vertical-align: middle; text-align: right;">
                                    <label><b>Personalized Greeting</b>
                                        <?= AppletUI::AudioSpeechPicker('no-answer-group-voicemail'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <tr class="radio-table-row last <?= ($no_answer_action === 'redirect') ? 'on' : 'off' ?>">
                <td class="radio-cell">
                    <input type="radio" class='no-answer-action-radio' name="no-answer-action"
                           value="redirect" <?= ($no_answer_action === 'redirect') ? 'checked="checked"' : '' ?>/>
                </td>
                <td class="content-cell" style="vertical-align: middle;">
                    <table>
                        <tr style="border-bottom-width: 0px;">
                            <td align="left" style="vertical-align: middle;"><h4>Go to</h4></td>
                            <td align="right">
                                <?= AppletUI::DropZone('no-answer-redirect') ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div class="vbx-full-pane nobody-answers-number <?= ($dial_whom_selector === 'number') ? '' : 'hide' ?>">
        <?= AppletUI::DropZone('no-answer-redirect-number') ?>
    </div>

    <!-- Set the version of this applet -->
    <input type="hidden" name="version" value="3"/>
</div><!-- .vbx-applet -->
