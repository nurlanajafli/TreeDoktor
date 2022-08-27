<div id="activenumbersCreateModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php $this->load->view('settings/integrations/twilio/active_numbers/form', [
                'activeNumber' => $data['activeNumber'],
                'applications' => $applications,
                'sid' => $sid,
                'errors' => $data['errors'] ?? [],
                'creatAction' => false
            ]);?>
        </div>
    </div>
</div>