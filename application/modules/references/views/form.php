<!-- Modal -->
<div class="modal fade referenceModal"  data-url="<?= base_url('references/save/');?>" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel"><strong></strong></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="form-horizontal bodyCreateEdit">
                    <div class="control-group">
                        <label class="control-label">Name</label>
                        <div class="controls">
                            <input type="hidden" name="reference_id" id="reference_id"/>
                            <input name="reference_name" id="reference_name" class="class_name form-control" type="text"
                                   value=""
                                   placeholder="Reference Name" style="background-color: #fff;">
                        </div>
                    </div>
                </div>
                <div class="bodyDelete" hidden></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" data-id="" class="btn btn-success saveReference">
                    <span class="btntext">Save</span>
                    <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
                         class="preloader">
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    $(document).on('click', '.triggerModalClass', function() {
        let referenceModal =  $('.referenceModal');
        referenceModal.find('#modalLabel').text('Create Reference');
        referenceModal.find('#reference_id').val('');
        referenceModal.find('#reference_name').val('');
        referenceModal.modal('toggle');
    });

    $(document).on('click', '.reference-edit-identity, .reference-delete-identity, .reference-restore-identity', function(e) {
        e.preventDefault();
        e.stopPropagation();

        $.ajax({
            type: $(this).data('request_type'),
            url: $(this).data('url'),
            dataType: 'json',
            data: {},
            success: function (response) {

                if (response.render_form) {
                    $('.referenceModal').find('#modalLabel').text('Edit Reference');
                    $('#reference_id').val(response.reference.id);
                    $('#reference_name').val(response.reference.name);
                    $('.referenceModal').modal('toggle');
                }
                if(response.data){
                    successMessage(response.message);
                    Common.request.get('/references/list', function (response) {
                        $('#references_list_wrapper').html(response.html);
                    })
                }
                if(response.status === 'error'){
                    errorMessage(response.message);
                }
            }
        });
    });
    //save form
    $(document).on('click', '.saveReference', function(e) {
        e.preventDefault();
        e.stopPropagation();

        $.ajax({
            type: 'POST',
            url: $('.referenceModal').data('url'),
            dataType: 'json',
            data: {id: $('#reference_id').val(), name: $('#reference_name').val()},
            success: function (response) {
                console.log(response, 'response');
                $('.referenceModal').modal('toggle');

                if(response.data){
                    successMessage(response.message);
                    Common.request.get('/references/list', function (response) {
                        $('#references_list_wrapper').html(response.html);
                    })
                }
                if(response.status === 'error'){
                    errorMessage(response.message);
                }

            }
        });
    });
</script>