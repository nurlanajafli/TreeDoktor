<?php

use application\modules\settings\integrations\twilio\libraries\AppletInstance;
use application\modules\settings\integrations\twilio\libraries\AppletUI;

$version = AppletInstance::getValue('version', null);

if (AppletInstance::getValue('dial-whom-selector', 'user-or-group') === 'user-or-group') {
    $showVoicemailAction = true;
} else {
    $showVoicemailAction = false;
}

$userOrGroup = AppletInstance::getUserGroupPickerValue('dial-whom-user-or-group');
$showGroupVoicemailPrompt = false;

$dial_whom_selector = AppletInstance::getValue('dial-whom-selector', 'user-or-group');
$no_answer_action = AppletInstance::getValue('no-answer-action', 'voicemail');

?>
<div class="vbx-applet dial-applet">

    <h2>Dial Whom</h2>
    <p>If a group is selected, all numbers will be dialed simultaneously. The first to answer will get the call.</p>
    <div class="radio-table">
        <table>
            <tr class="radio-table-row first <?= ($dial_whom_selector === 'user-or-group') ? 'on' : 'off' ?>">
                <td class="radio-cell">
                    <input type="radio" class='dial-whom-selector-radio' name="dial-whom-selector"
                           value="user-or-group" <?= ($dial_whom_selector === 'user-or-group') ? 'checked="checked"' : '' ?> />
                </td>
                <td class="content-cell">
                    <h4>Dial a user or group</h4>
                    <?= AppletUI::UserGroupPicker('dial-whom-user-or-group'); ?>
                </td>
            </tr>
            <tr class="radio-table-row last <?= ($dial_whom_selector === 'number') ? 'on' : 'off' ?>">
                <td class="radio-cell">
                    <input type="radio" class='dial-whom-selector-radio' name="dial-whom-selector"
                           value="number" <?= ($dial_whom_selector === 'number') ? 'checked="checked"' : '' ?> />
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
    <h2>If nobody answers...</h2>
    <div class="radio-table no-answer nobody-answers-user-group <?= ($dial_whom_selector === 'user-or-group') ? '' : 'hide' ?>">
        <table>
            <tr class="voicemail-row radio-table-row first <?= ($no_answer_action === 'voicemail') ? 'on' : 'off' ?> <?= $showVoicemailAction ? '' : 'hide' ?>">
                <td class="radio-cell">
                    <input type="radio" class='no-answer-action-radio' name="no-answer-action"
                           value="voicemail" <?= ($no_answer_action === 'voicemail') ? 'checked="checked"' : '' ?> />
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
                           value="redirect" <?= ($no_answer_action === 'redirect') ? 'checked="checked"' : '' ?> />
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
