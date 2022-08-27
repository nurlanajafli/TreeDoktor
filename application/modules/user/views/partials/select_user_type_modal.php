<div class="modal fade" id="select-user-type" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title">User Type</h4>
      </div>
      <div class="modal-body p-bottom-0 p-top-2">
          <div class="row">
            <div class="col-sm-6 col-md-6 padder-v b-r b-light text-center">
                <a class="clear" href="<?php echo base_url('/user/user_add/support'); ?>">
                    <span class="fa-stack fa-2x m-r-sm">
                        <i class="fa fa-circle fa-stack-2x text-info"></i>
                        <i class="fa fa-male fa-stack-1x text-white"></i>
                    </span>
                    <span class="h3 block m-t-xs"><strong>Support</strong></span>
                </a>
            </div>
            <div class="col-sm-6 col-md-6 padder-v b-r b-light text-center">
                <a class="clear" href="<?php echo base_url('/user/user_add/field'); ?>">
                    <span class="fa-stack fa-2x m-r-sm">
                        <i class="fa fa-circle fa-stack-2x text-warning"></i>
                        <i class="fa fa-male fa-stack-1x text-white"></i>
                    </span>
                    <span class="h3 block m-t-xs"><strong>Field</strong></span>
                </a>
            </div>
          </div>
      </div>
      <div class="modal-footer">
        <a href="#" class="btn btn-default" data-dismiss="modal">Close</a>
      </div>
      

    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>