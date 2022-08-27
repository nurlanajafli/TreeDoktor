<?php

use application\modules\settings\integrations\twilio\libraries\AppletUI;

?>
<div class="vbx-applet">

    <h2>Forward SMS Messages to</h2>
    <?= AppletUI::UserGroupPicker('forward'); ?>

</div><!-- .vbx-applet -->
