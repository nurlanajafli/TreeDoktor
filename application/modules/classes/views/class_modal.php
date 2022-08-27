<!-- Modal -->

<div class="modal fade classModal"  tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel"><strong></strong></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

                <div class="modal-body">
                    <div class="form-horizontal bodyCreateEdit" hidden>
                        <div class="control-group">
                            <label class="control-label">Name</label>
                            <div class="controls">
                                <input name="className" class="class_name form-control" type="text"
                                       value=""
                                       placeholder="Class Name" style="background-color: #fff;">
                            </div>
                        </div>
                        <div class="control-group parentClass">
                            <label class="control-label">Parent Class</label>
                            <div class="controls">
                                <input type="text" class="classesSelectParent w-100" value="" name="classParentId"/>
                            </div>
                        </div>
                    </div>
                    <div class="bodyDelete" hidden></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" data-id="" class="btn btn-success saveClass">
                        <span class="btntext">Save</span>
                        <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
                             class="preloader">
                    </button>
                </div>

        </div>
    </div>
</div>
