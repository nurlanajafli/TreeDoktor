<div id="start-work-modal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Team Lead Signature</h4>
      </div>
      <div class="modal-body">
        <div id="signature-pad" class="signature-pad">
          <div class="signature-pad--body">
            <canvas></canvas>
          </div>
          <div class="signature-pad--footer">
            <?php /*
            <div class="description">Sign above</div>
            */ ?>
            <div class="signature-pad--actions">
                <button type="button" class="button clear btn btn-success" data-action="clear">Clear&nbsp;<i class="fa fa-eraser"></i></button>
                <button type="button" class="button btn btn-info" data-action="undo">Undo&nbsp;<i class="fa fa-undo"></i></button>

              
              <button type="button" class="button" data-action="change-color">Change color</button>
              <div>
                <button type="button" class="button save" data-action="save-png">Save as PNG</button>
                <button type="button" class="button save" data-action="save-jpg">Save as JPG</button>
                <button type="button" class="button save" data-action="save-svg">Save as SVG</button>
              </div>
              
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-dismiss="modal">Start</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
