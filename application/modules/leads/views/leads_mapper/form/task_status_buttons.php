{{if status!='new'}}
<textarea placeholder="This field is required..." name="text" class='form-control m-t-sm m-b-sm'></textarea>

<div class="text-right m-bottom-5">
    <button type="reset" class="btn btn-default" style="padding: 6px 12px; margin-right:10px;">Close</button>
    <button type="submit"  class="btn btn-success" style="padding: 6px 12px;">
        <span class="btntext">Save</span>
        <img src="<?php echo base_url("assets/img/ajax-loader.gif"); ?>" style="display: none;width: 32px;" class="preloader">
    </button>
</div>
{{/if}}