<div class="modal modal-static fade" id="processing-modal" role="dialog" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog" style="width:178px;margin:10% auto;">
		<div class="modal-content" style="background: none;box-shadow: none;border: none;">
			<div class="modal-body">
				<div class="text-center" style="position: relative;">
                    <i class="fa fa-spinner fa fa-spin fa fa-5x" style="font-size: 11em;color: #97cf74;"></i>
                    <img src="<?php echo base_url('assets/img/processing_modal.svg'); ?>" width="60px" height="60px" style="position: absolute;left: 37px;top: 40px;z-index: -1;" class="icon" />
					<?php /*<h4>Processing ...</h4>*/?>
				</div>
			</div>
		</div>
	</div>
</div>
<style>
    #processing-modal{
        background-color: rgb(122 122 122 / 25%);
    }
</style>