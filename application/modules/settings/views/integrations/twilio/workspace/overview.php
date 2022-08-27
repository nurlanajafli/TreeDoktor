<?php
/** @var string $workspaceSid */
/** @var Twilio\Rest\Taskrouter\V1\WorkspaceInstance $workspace */
?>
<?php $this->load->view('includes/header'); ?>
    <link rel="stylesheet" href="<?= base_url(); ?>assets/css/modules/soft_twilio_calls/workspace.css" type="text/css"/>
    <section class="hbox stretch twilio-workspace-section">
        <section id="content">
            <section class="vbox">
                <section class="scrollable wrapper">

                    <div class="panel panel panel-default p-n">
                        <header class="header bg-gradient bg-white">
                            <p class="h4 text-success pull-left"><i class="fa fa-cogs"></i> Workspace Settings</p>
                            <div class="clearfix"></div>
                        </header>
                    </div>
                    <div class="row twilio-index-content">
                        <div class="col-lg-2">
                            <?php $this->load->view('settings/integrations/twilio/workspace/sidebar', ['workspaceSid' => $workspaceSid]); ?>
                        </div>
                        <div class="col-lg-10 p-left-0">
                            <?php $this->load->view('settings/integrations/twilio/workspace/update', [
                                'workspace' => $workspace,
                                'workspaceSid' => $workspaceSid
                            ]);?>
                        </div>
                    </div>
                </section>
            </section>
        </section>
    </section>

<?php $this->load->view('includes/footer'); ?>