<script>
    var mapCircles = <?php echo is_array(config_item('leads_circles')) ? json_encode(config_item('leads_circles')) : json_encode([]); ?>;
    var mapCirclesArr = [];
</script>
<div class="modal fade" id="schedule-appointment-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog w-90">
    <div class="modal-content">
      
      <div class="modal-body p-bottom-0 p-top-2"></div>
        <input class="hidden" name="notify_client_email_test">

    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>

<script type="text/x-jsrender" id="appointment-type-template">
  <option value="{{:category_id}}">{{:category_name}}</option>
</script>
<script type="text/x-jsrender" id="appointment-lead-template">
  <option value="{{:lead_id}}" data-lat="{{:latitude}}" data-lon="{{:longitude}}">{{:lead_no}}</option>
</script>
<script type="text/x-jsrender" id="appointment-estimator-template">
  <option value="{{:id}}">{{:emp_name}}</option>
</script>

<script type="text/x-jsrender" id="task-infowindow-tmp">
    <?php $this->load->view('clients/appointment/task_infowindow'); ?>
</script>

<div id="map-infowindow" class="hidden"></div>

<style type="text/css">
  .estimator-radio{
    display: flex!important;
    flex-direction: row-reverse;
    justify-content: space-between;
    padding: 8px 10px 12px 30px;
  }
  .p-top-0{ padding-top: 2px!important; }
</style>