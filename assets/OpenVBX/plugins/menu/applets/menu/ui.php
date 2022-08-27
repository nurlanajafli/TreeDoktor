<?php

use application\modules\settings\integrations\twilio\libraries\AppletInstance;
use application\modules\settings\integrations\twilio\libraries\AppletUI;

$defaultNumberOfChoices = 2;
$keys = (array)AppletInstance::getValue('keys[]', ['1', '2']);
$choices = (array)AppletInstance::getValue('choices[]');
?>

<div class="vbx-applet menu-applet">

    <h2>Menu Prompt</h2>
    <p>When the caller reaches this menu they will hear:</p>
    <div class="menu-prompt">
        <?= AppletUI::audioSpeechPicker('prompt'); ?>
    </div>
    <br/>
    <h2>Menu Options</h2>
    <table class="vbx-menu-grid options-table">
        <thead>
        <tr>
            <td>Keypress</td>
            <td>&nbsp;</td>
            <td>Applet</td>
            <td>Add &amp; Remove</td>
        </tr>
        </thead>
        <tfoot>
        <tr class="hide">
            <td>
                <fieldset class="vbx-input-container">
                    <input class="keypress tiny" type="text" name="new-keys[]" value="" autocomplete="off"/>
                </fieldset>
            </td>
            <td>then</td>
            <td>
                <?= AppletUI::dropZone('new-choices[]', 'Drop applet here'); ?>
            </td>
            <td>
                <a href="" class="add action fa fa-plus-circle"><span class="replace">Add</span></a>
                <a href="" class="remove action fa fa-minus-circle"><span class="replace">Remove</span></a>
            </td>
        </tr>
        </tfoot>
        <tbody>
        <?php foreach ($keys as $i => $key): ?>
            <tr>
                <td>
                    <fieldset class="vbx-input-container">
                        <input class="keypress tiny" type="text" name="keys[<?= $key; ?>]"
                               value="<?php echo $key ?>" autocomplete="off"/>
                    </fieldset>
                </td>
                <td>then</td>
                <td>
                    <?= AppletUI::dropZone('choices[' . ($i) . ']', 'Drop applet here'); ?>
                </td>
                <td>
                    <a href="" class="add action fa fa-plus-circle"><span class="replace">Add</span></a>
                    <a href="" class="remove action fa fa-minus-circle"><span class="replace">Remove</span></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table><!-- .vbx-menu-grid -->

    <h3>Do you want to repeat the menu back?</h3>
    <div class="vbx-full-pane">
        <p>Repeat the menu back to the caller. Enter zero if you do not want the menu to repeat.</p>
        <fieldset class="vbx-input-complex vbx-input-container">
            <input type="text" name="repeat-count" class="left tiny"
                   value="<?php echo AppletInstance::getValue('repeat-count', 3) ?>"/>
            <label class="field-label-left">time(s)</label>
        </fieldset>
    </div>

    <h3>Gather timeout?</h3>
    <div class="vbx-full-pane">
        <p>Timeout allows you to set the limit (in seconds) that Twilio will wait for the caller to press another digit.</p>
        <fieldset class="vbx-input-complex vbx-input-container">
            <input type="text" name="timeout" class="left tiny"
                   value="<?php echo AppletInstance::getValue('timeout', 5) ?>"/>
            <label class="field-label-left">second(s)</label>
        </fieldset>
    </div>

    <h3>When the caller didn't enter anything after the menu...</h3>
    <div class="vbx-full-pane">
        <fieldset class="vbx-input-complex vbx-input-container">
            <p>Redirect the caller to another applet.</p>
            <?= AppletUI::DropZone('next'); ?>
        </fieldset>
    </div><!-- .vbx-split-pane -->

    <h3>Oops! The caller didn't enter something right.</h3>
    <p>Customize a specific message about the invalid option.</p>
    <?= AppletUI::audioSpeechPicker('invalid-option'); ?>
    <br/>

</div><!-- .vbx-applet -->
