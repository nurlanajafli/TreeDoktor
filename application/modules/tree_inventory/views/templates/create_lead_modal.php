<!-- Modal -->
<div class="modal fade" id="createLeadModal" tabindex="-1" role="dialog" aria-labelledby="createLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="col-md-2"></div>
        <div class="modal-content panel panel-xs panel-default col-md-6" style="border-radius: 8px">
            <div class="modal-body p-left-0 p-right-0 p-top-10">
                <div class="form-group m-bottom-5">
                    <label for="leads_select" class="col-sm-12 p-left-0 p-bottom-5 h5"><strong>Choose a lead or create a new one</strong></label>
                    <select style="border-radius: 8px; background-color: white;" id="leads_select" class="col-sm-12 form-control">
                        <option selected value="new">Create a new lead</option>
                        <?php foreach (json_decode($leads) as $lead): ?>
                            <option value="<?= $lead->lead_id ?>"><?= $lead->lead_no ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group" style="text-align: center; padding-top: 40px; margin-bottom: 10px">
                <button class="btn btn-success m-right-10" id="save_lead" data-dismiss="modal" style="border-radius: 8px; width: 100px"><span class="btntext">Save</span></button>
                <button class="btn text-danger m-left-10" data-dismiss="modal" style="border-radius: 8px; background-color: white; border: 1px solid #CC806A; width: 100px" aria-hidden="true">Cancel</button>
            </div>
        </div>


    </div>
</div>