<?php
/** @var Twilio\Rest\Taskrouter\V1\WorkspaceInstance $workspace */
?>
<section class="scrollable p-sides-10">

    <section id="soft_twilio_calls_wrapper" class="panel panel-default" style="min-height: 60px;">

        <div class="row">
            <div class="col-sm-12">
                <section class="panel panel-default">
                    <div class="panel-body">
                        <?php $this->load->view('settings/integrations/twilio/workspace/form', [
                            'workspace' => $workspace,
                            'errors' => $data['errors'] ?? []
                        ]);?>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>

