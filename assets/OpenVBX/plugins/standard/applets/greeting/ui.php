<?php

use application\modules\settings\integrations\twilio\libraries\AppletUI;

?>
<div class="vbx-applet">

    <h2>Audio Choice</h2>
    <p>When the caller reaches this prompt, they will hear:</p>
    <?= AppletUI::AudioSpeechPicker('prompt'); ?>

    <br/>

    <h2 class="settings-title">Next</h2>
    <p>After the initial prompt, continue to the next applet</p>
    <div class="vbx-full-pane">
        <?= AppletUI::DropZone('next'); ?>
    </div><!-- .vbx-full-pane -->

</div><!-- .vbx-applet -->
