
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
                        <section class="scrollable p-sides-10">
                            <section id="soft_twilio_calls_wrapper" class="panel panel-default" style="min-height: 60px;">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <section class="panel panel-default">
                                            <div class="panel-body">
                                                <?php $this->load->view('settings/integrations/twilio/task_queue/form', [
                                                    'taskQueue' => $data['taskQueue'],
                                                    'errors' => $data['errors'] ?? [],
                                                    'unavailableActivities' => $data['unavailableActivities'],
                                                    'creatAction' => false,
                                                    'workspaceSid' => $workspaceSid
                                                ]);?>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                            </section>
                        </section>
                    </div>
                </div>
            </section>
        </section>
    </section>
</section>

<script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/task_queue.js"></script>

<?php $this->load->view('includes/footer'); ?>
