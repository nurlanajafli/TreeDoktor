<?php
$this->load->view('includes/header');

if (isset($flow_data)) {
    printf('<script type="text/javascript">var flow_data = %s;</script>', json_encode($flow_data));
}
?>
    <link rel="stylesheet" href="<?= base_url(); ?>assets/css/modules/soft_twilio_calls/flows.css" type="text/css"/>
    <link rel="stylesheet" href="<?= base_url(); ?>assets/css/modules/soft_twilio_calls/master.css" type="text/css"/>
    <link rel="stylesheet" href="<?= base_url(); ?>assets/css/modules/soft_twilio_calls/buttons.css" type="text/css"/>
    <link rel="stylesheet" href="<?= base_url('assets/vendors/kartik-v/fileinput/css/fileinput.css'); ?>"
          type="text/css"/>
    <div id="prototypes">
        <?php foreach ($applets as $applet): ?>
            <?php
            if (!empty($applet->style_url)) {
                $url = '/' . $applet->style_url;
                $filepath = (preg_match('/^(https?:\/\/|\/\/)/', $url) ? $url : site_url($url));
                echo '<link rel="stylesheet" href="' . $filepath . '">';
            }
            if (!empty($applet->script_url)) {
                $url = '/' . $applet->script_url;
                $filepath = (preg_match('/^(https?:\/\/|\/\/)/', $url) ? $url : site_url($url));
                echo '<script src="' . $filepath . '"></script>';
            }
            ?>
            <div id="prototype-<?php echo $applet->id ?>"
                 class="flow-instance content-section <?php echo $applet->id ?> <?php echo $applet->css_class_name ?> hide">
                <a href="" class="minimize fa fa-minus-square close-flow-instance" title="Minimize"><span
                            class="replace">Minimize</span></a>
                <h2 class="applet-name"><?php echo $applet->name ?></h2>
                <div class="settings-panel">
                    <?php echo $applet->render($flow->id); ?>
                </div><!-- .settings-panel -->
            </div><!-- .content-section -->
        <?php endforeach; ?>

    </div><!-- #prototypes -->

    <div id="flow-meta">
        <div id="flow-<?php echo $flow->id ?>" class="flow-id"></div>
    </div><!-- #flow-meta -->
    <section class="scrollable p-sides-10">
        <div action="<?php echo site_url('settings/integrations/twilio/save'); ?>" method="post" class="vbx-form <?php echo $editor_type ?>">
        <input type="hidden" name="flow-name" class="flow-name" value="<?php echo $flow->name ?>"/>
        <input type="hidden" name="flow-id" class="flow-id" value="<?php echo $flow->id ?>"/>
        <div class="vbx-content-container">

            <div class="yui-ge">

                <div class="yui-u first">

                    <div id="flowline">

                        <div id="instances">

                            <table id="instance-table">

                                <tr id="instance-row">

                                    <?php foreach ($flow_data as $instance_id => $instance): ?>
                                        <?php $applet = isset($applets[$instance->type]) ? $applets[$instance->type] : null; ?>
                                        <?php if (is_object($applet)): ?>
                                            <?php $template = $applet->render($flow->id, $instance); ?>
                                            <td class="instance-cell">
                                                <form>
                                                    <div id="<?php echo $instance->id ?>"
                                                         rel="<?php echo $applet->id ?>"
                                                         class="flow-instance <?php echo $applet->id ?> <?php echo $applet->css_class_name ?> hide">
                                                        <?php if ($instance_id != "start"): ?>
                                                            <a href=""
                                                               class="minimize fa fa-minus-square close-flow-instance"
                                                               title="Minimize"><span
                                                                        class="replace">Minimize</span></a>
                                                        <?php endif; ?>
                                                        <h2 class="applet-name"><?php echo ($editor_type == 'voice') ? $applet->voice_title : (($editor_type == 'sms') ? $applet->sms_title : $applet->title); ?></h2>
                                                        <div class="settings-panel vbx-applet">
                                                            <?php echo $template ?>
                                                            <!--<a class="view-source" target="_new"
                                                               href="<?php echo site_url('twiml/applet/' . $editor_type . '/' . $flow->id . '/' . $instance->id) ?>">View
                                                                TwiML</a>-->
                                                        </div><!-- .settings-panel -->
                                                    </div><!-- .flow-instance -->
                                                </form>
                                            </td><!-- .instance-cell -->

                                        <?php endif; ?>
                                    <?php endforeach; ?>

                                </tr><!-- #instance-row -->

                            </table><!-- #instance-table -->

                        </div><!-- #instances -->

                    </div><!-- #flowline -->

                </div> <!-- .yui-u .first -->

                <div class="yui-u" style="position: fixed;display: inline;">

                    <div id="items-toolbox" style="display: table;">
                        <?php $type = substr_replace($editor_type, strtoupper(substr($editor_type, 0, 1)), 0, 1); ?>
                        <h3><?php echo $type; ?> Applets</h3>
                        <?php foreach ($applets as $applet) : ?>
                            <?php if (!$applet->disabled && $applet->visible === true && in_array($editor_type,
                                    $applet->type)): ?>
                                <a rel="<?php echo $applet->id ?>" data-applet_icon="<?= $applet->icon ?? '' ?>"
                                   class="applet-item"
                                   title="<?php echo $applet->description ?>">
                                <span id="<?php echo $applet->id ?>" class="applet-icon <?= $applet->icon ?? '' ?>"
                                      style=" background: url(<?= empty($applet->icon) ? $applet->icon_url : '' ?>) no-repeat center center;">
                                    <span class="replace">
                                        <?php echo ($editor_type == 'voice') ? $applet->voice_name : (($editor_type == 'sms') ? $applet->sms_name : $applet->name); ?>
                                    </span>
                                </span>
                                    <span class="applet-name"><?php echo ($editor_type == 'voice') ? $applet->voice_name : (($editor_type == 'sms') ? $applet->sms_name : $applet->name); ?></span>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>

                    </div>
                    
                    <div>
                        <ul class="vbx-menu-items-right flow-form-buttons-applets">
                            <li class="menu-item"><a class="btn btn-success save-button" href=""><span>Save</span></a></li>
                            <li class="menu-item">
                                <a class="btn btn-default close-button"
                                   href="<?php echo base_url('/settings/integrations/twilio') ?>">
                                    <span>Close</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div><!-- .yui-ge 3/4, 1/4 -->

        </div><!-- .content-container -->
        <div id="dialog-templates" style="display: none">
            <div id="dialog-app-delete" class="dialog hide" title="Delete applet">
                <p>Are you sure you want to delete this applet?</p>
            </div>

            <div id="dialog-save-as" class="dialog" title="Save As&hellip;">
                <p>What would you like to save this flow as?</p>
                <div class="vbx-input-container">
                    <label class="field-label">Flow Name
                        <input type="text" class="medium" name="name" value=""/>
                    </label>
                </div>
            </div>

            <div id="dialog-select-audio" class="dialog hide" title="Select audio">
            </div>

            <div id="dialog-replace-applet" class="dialog hide" title="Replace Applet">
                <p>Are you sure you wish to replace this applet? All children of this applet will lose their
                    connections.</p>
            </div>

            <div id="dialog-remove-applet" class="dialog hide" title="Remove Applet">
                <p>Are you sure you wish to remove this applet? All children of this applet will lose their
                    connections.</p>
            </div>

            <div id="dialog-close" class="dialog hide" title="Flow Modified">
                <p>Would you like to save your changes before closing the editor?</p>
            </div>
        </div>
    </div>
    </section>
    <div id="taskQueueModalForm"><!-- Load partial soft_twilio_calls/task_queue_modal_form by ajax --></div>

    <script type="text/javascript">
        // global params
        window.OpenVBX = {home: null, assets: null, client_capability: null, client_params: null};
        OpenVBX.home = '<?php echo preg_replace("|/$|", "", site_url('')); ?>';
        OpenVBX.assets = '<?php echo site_url(''); ?>';
        <?php if (isset($client_capability) && $client_capability): ?>
        OpenVBX.client_capability = '<?php echo $client_capability; ?>';
        <?php endif; ?>
        <?php
        if (isset($openvbx_js) && !empty($openvbx_js)) {
            foreach ($openvbx_js as $var => $val) {
                // wrap output in quotes, with exceptions
                if (!is_int($val) && !is_bool($val) && !preg_match('|^{.*}$|', $val)) {
                    $val = '"' . $val . '"';
                }
                echo "\tOpenVBX.{$var} = {$val};" . PHP_EOL;
            }
        }
        ?>
    </script>


    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/soundmanager2/soundmanager2.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/swfupload/swfupload.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/swfupload/swfupload.cookies.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/modal-tabs.js"></script>
    <!--<script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/plugins/jquery.cookie.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/plugins/jquery.validate.js"></script>-->
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/plugins/json.js"></script>
    <script src="<?php echo base_url(); ?>assets/vendors/kartik-v/fileinput/js/fileinput.min.js"></script>

    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/plugins/call-and-sms-dialogs.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/plugins/flicker.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/plugins/jquery.ba-hashchange.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/plugins/jquery.livequery.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/plugins/buttonista.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/plugins/jquery.animateToClass.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/plugins/static.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/plugins/jquery.swfupload.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/plugins/jquery.tabify.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/plugins/jquery.timePicker.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/global.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/sound.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/pickers.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/messages.js"></script>

    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/flow.js"></script>

<?php $this->load->view('includes/footer'); ?>