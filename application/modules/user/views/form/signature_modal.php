<div class="modal fade" id="signature-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title">Signature</h4>
      </div>
      <div class="modal-body p-bottom-0 p-top-2" style="height: 510px;">
          
            <label class="  control-label">Signature:</label>
            <div id="epiceditor"  style="height: 490px;"></div>
            <textarea id="epiceditor-content" style="display: none;" name="user_signature"><?php echo isset($user_row->user_signature) ? $user_row->user_signature : $this->input->post('user_signature');?></textarea>
          
      </div>
      <div class="modal-footer">
          <a href="#" class="btn btn-primary" data-dismiss="modal">ok</a>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>
