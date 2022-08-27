<div id="flowCreateModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <form name="modalFlowForm" action="<?= base_url('/settings/integrations/twilio/create'); ?>">
                <div class="modal-body">
                    <h5 class="p-bottom-20">Flow</h5>
                    <table class="table table-striped b-a b-light m-t-n-xxs m-b-none">
                        <tbody><tr>
                            <td class="w-200">
                                <label class="control-label">Flow Name:</label>
                            </td>
                            <td class="p-left-30">
                                <input type="text" name="name" value="" class="form-control">
                            </td>
                        </tr>
                        <tr>
                            <td class="w-200">
                                <label class="control-label">Application:</label>
                            </td>
                            <td class="p-left-30">
                                <?php if ($applications):?>
                                    <select class="form-control" name="application_id">
                                        <option value="0">Set Application</option>
                                        <?php foreach ($applications as $value):?>
                                            <option value="<?=$value->id?>"><?=$value->friendlyName?></option>
                                        <?php endforeach;?>
                                    </select>
                                <?php endif;?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <p class="hidden form-attribute-invalid error alert-danger"></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Close</button>
                    <input type="submit" name="submit" value="Save" class="btn btn-success update__client">
                </div>
            </form>
        </div>
    </div>
</div>