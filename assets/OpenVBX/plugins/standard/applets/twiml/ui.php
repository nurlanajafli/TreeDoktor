<?php
use application\modules\settings\integrations\twilio\libraries\AppletInstance;
use application\modules\settings\integrations\twilio\libraries\AppletUI;

?>
<div class="vbx-applet">

		<h2>Build your own TwiML</h2>
		<p><a href="http://www.twilio.com/docs/api/2010-04-01/twiml/" target="_blank">Learn more about TwiML</a></p>
		<fieldset class="vbx-input-container">
			<textarea name="twiml" class="large" placeholder="&lt;Say&gt;:)&lt;/Say&gt;"><?= AppletInstance::getValue('twiml') ?></textarea>
		</fieldset>


		<h2 class="settings-title">Next</h2>
		<p>After the message is sent, continue to the next applet</p>
		<div class="vbx-full-pane">
			<?= AppletUI::DropZone('next'); ?>
		</div>

</div><!-- .vbx-applet -->
