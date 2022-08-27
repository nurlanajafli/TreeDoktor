<div id="applicationModalAction" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <form name="modalApplicationForm" role="form" method="post" action="<?=base_url('/settings/integrations/twilio/application/create')?>">
                <?php $this->load->view('settings/integrations/twilio/application/form', [
                    'errors' => $data['errors'] ?? [],
                    'creatAction' => true
                ]);?>
            </form>
        </div>
    </div>
</div>
