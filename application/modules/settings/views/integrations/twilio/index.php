<?php $this->load->view('includes/header'); ?>
    <link rel="stylesheet" href="<?= base_url(); ?>assets/css/modules/soft_twilio_calls/main_page.css" type="text/css"/>
    <section class="hbox stretch twilio-main-section">
        <section id="content">
            <section class="vbox">
                <section class="scrollable wrapper">

                    <div class="panel panel panel-default p-n">
                        <header class="header bg-gradient bg-white">
                            <p class="h4 text-success pull-left"><i class="fa fa-cogs"></i> Twilio Settings</p>
                            <div class="clearfix"></div>
                        </header>
                    </div>

                    <div class="row twilio-index-content">

                        <div class="col-lg-6">

                            <section class="panel panel-default">
                                <header class="panel-heading" style="display: flow-root;">
                            <span class="pull-right">
                                <a href="#flowCreateModal" class="btn btn-xs btn-primary add-flow" data-toggle="modal">
                                    <i class="fa fa-plus"></i>
                                </a>
                            </span>
                                    Workflows
                                </header>
                                <table class="table table-striped m-b-none">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone Numbers</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($flows as $flow): ?>
                                        <tr>
                                            <td><?= $flow->name ?></td>
                                            <td>None</td>
                                            <td class="text-right">
                                                <div class="btn-group">
                                                    <a class="edit-flow"
                                                       href="<?= site_url("/settings/integrations/twilio/edit/{$flow->id}/voice#flowline/start"); ?>"
                                                       style="margin-right: 10px">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <a href="/settings/integrations/twilio/delete/<?= $flow->id; ?>"
                                                       class="deleteFlow trash action" title="Delete">
                                                        <i class="text-danger fa fa-trash-o"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </section>

                        </div>

                        <div class="col-lg-6">
                            <section class="panel panel-default">
                                <header class="panel-heading" style="display: flow-root;">
                            <span class="pull-right" style="visibility: hidden">
                                <a href="#" class="btn btn-xs btn-primary"><i class="fa fa-plus"></i></a>
                            </span>
                                    Active Numbers
                                </header>
                                <table class="table table-striped m-b-none">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Friendly Name</th>
                                        <th width="70"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($activeNumbers as $activeNumber): ?>
                                        <tr>
                                            <td><?= $activeNumber->phoneNumber ?></td>
                                            <td><?= $activeNumber->friendlyName ?></td>
                                            <td class="text-right">
                                                <div class="btn-group">
                                                    <a href="/settings/integrations/twilio/active-numbers/update/<?= $activeNumber->sid; ?>"
                                                       class="edit-activenumbers">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </section>
                        </div>

                        <div class="col-lg-12">
                            <section class="panel panel-default">
                                <header class="panel-heading" style="display: flow-root;">
                                    <span class="pull-right"
                                          style="<?= empty($workspaces) ? 'visibility: hidden' : '' ?>">
                                        <a href="<?= base_url('/settings/integrations/twilio/workspace/create') ?>"
                                           class="btn btn-xs btn-primary create-workspace"><i
                                                    class="fa fa-plus"></i></a>
                                    </span>
                                    Workspaces
                                </header>
                                <table class="table table-striped m-b-none">
                                    <thead>
                                    <tr>
                                        <th>Workspace</th>
                                        <th>Default activity</th>
                                        <th>Timeout activity</th>
                                        <th>Event callback url</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($workspaces as $workspace): ?>
                                        <tr>
                                            <td><?= $workspace->friendlyName ?></td>
                                            <td><?= $workspace->defaultActivityName ?></td>
                                            <td><?= $workspace->timeoutActivityName ?></td>
                                            <td><?= $workspace->eventCallbackUrl ?></td>
                                            <td class="text-right">
                                                <div class="btn-group">
                                                    <a href="/settings/integrations/twilio/workspace/overview/<?= $workspace->sid; ?>"
                                                       class="edit action" title="Edit">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <a href="/settings/integrations/twilio/workspace/delete/<?= $workspace->sid; ?>"
                                                       class="trash action delete-workspace" title="Delete">
                                                        <i class="text-danger fa fa-trash-o"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </section>
                        </div>

                        <div class="col-lg-12">
                            <section class="panel panel-default">
                                <header class="panel-heading" style="display: flow-root;">
                                    <span class="pull-right">
                                        <a href="<?= base_url('/settings/integrations/twilio/application/create') ?>"
                                           class="btn btn-xs btn-primary create-application"><i class="fa fa-plus"></i></a>
                                    </span>
                                    Twiml Application
                                </header>
                                <table class="table table-striped m-b-none">
                                    <thead>
                                    <tr>
                                        <th>Twiml App Name</th>
                                        <th>SID</th>
                                        <th>Voice Url</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($applications as $application): ?>
                                        <tr>
                                            <td><?= $application->friendlyName ?></td>
                                            <td><?= $application->sid ?></td>
                                            <td><?= $application->voiceUrl ?></td>
                                            <td class="text-right">
                                                <div class="btn-group">
                                                    <a href="/settings/integrations/twilio/application/update/<?= $application->sid; ?>"
                                                       class="edit action edit-application" title="Edit">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <a href="/settings/integrations/twilio/application/delete/<?= $application->sid ?>"
                                                       class="trash action delete-application" title="Delete">
                                                        <i class="text-danger fa fa-trash-o"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </section>
                        </div>

                        <div class="col-lg-12">
                            <section class="panel panel-default">
                                <header class="panel-heading" style="display: flow-root;">
                                    <?php if ($isSmsInstalled === false): ?>
                                        <span class="pull-right">
                                            <a href="<?= base_url('/settings/integrations/twilio/install/sms') ?>"
                                               class="btn btn-xs btn-primary">Install</a>
                                        </span>
                                    <?php else: ?>
                                        <span class="pull-right">
                                            <a href="<?= base_url('/settings/integrations/twilio/sms/uninstall') ?>"
                                               class="btn btn-xs btn-danger uninstall-sms"><i class="fa fa-minus"></i></a>
                                        </span>
                                    <?php endif; ?>

                                    <span>Sms Twilio Settings <input type="checkbox"
                                                                     id="sms_messenger" <?= $isMessangerShow === true ? 'checked' : '' ?> name="messenger"
                                                                     placeholder="" class=""/></span>
                                </header>
                                <?php if ($isSmsInstalled === true): ?>
                                    <table class="table table-striped m-b-none">
                                        <thead>
                                        <tr>
                                            <th>Messaging Service</th>
                                            <th>Messaging Service Sid</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php if(!empty($messagingServices)):?>
                                            <?php foreach ($messagingServices as $messagingService): ?>
                                                <tr>
                                                    <td><?= $messagingService->friendlyName ?></td>
                                                    <td><?= $messagingService->sid ?></td>
                                                    <td class="text-right">
                                                        <div class="btn-group">
                                                            <a href="/settings/integrations/twilio/messaging-services/update/<?= $messagingService->sid; ?>"
                                                               class="edit action edit-messaging_service" title="Edit">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
                                                            <a href="/settings/integrations/twilio/messaging-services/delete/<?= $messagingService->sid; ?>"
                                                               class="trash action delete-messaging_service" title="Delete">
                                                                <i class="text-danger fa fa-trash-o"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td class="text-right">
                                                    <div class="btn-group">
                                                        <a href="/settings/integrations/twilio/messaging-services/create/"
                                                           class="edit action create-messaging_service" title="Edit">
                                                            <i class="fa fa-plus"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif;?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </section>
                        </div>

                    </div>

                </section>
            </section>
        </section>
    </section>

    <div id="flow-modal"></div>
    <div id="flow-delete">
        <div id="deleteModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
             aria-hidden="true" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form name="modalDeleteNotify" action="<?= base_url('/settings/integrations/twilio/delete'); ?>">
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
    <div id="active-numbers-modal"></div>
    <div id="workspace-modal"></div>
    <div id="application-modal"></div>
    <div id="messaging-services-modal"></div>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/flows.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/workspace.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/application.js"></script>

<?php $this->load->view('includes/footer'); ?>