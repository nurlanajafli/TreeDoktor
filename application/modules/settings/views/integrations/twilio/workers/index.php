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
                        <?php $this->load->view('settings/integrations/twilio/workers/list', [
                            'workers' => $workers,
                            'workspaceSid' => $workspaceSid,
                            'availableUsers' => $availableUsers
                        ]);?>
                    </div>
                </div>
            </section>
        </section>
    </section>
</section>

<div id="worker-delete">
    <div id="deleteModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <form name="modalDeleteNotify" action="">
                    <div class="modal-body">
                        <h5 class="p-bottom-20">Are you sure you wish to delete this?</h5>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Close</button>
                        <input type="submit" name="submit" value="Delete" class="btn btn-danger">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/workers.js"></script>

<?php $this->load->view('includes/footer'); ?>

