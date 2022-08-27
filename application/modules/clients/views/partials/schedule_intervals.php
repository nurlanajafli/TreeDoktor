<?php if(isset($intervals) && !empty($intervals)): ?>
<div class="panel-group m-b" id="accordion2">
  <?php if(isset($task_author_id)): ?>
  <?php foreach($intervals as $key=>$interval): ?>                  

  <div class="panel panel-default schedule-interval-container">
    <div class="panel-heading text-center p-n ">
      
      <div class="radio m-n p-top-10 p-bottom-10">
        <label class="pull-left">Free time:</label>
        <label class="radio-custom">
          <input type="radio" name="time_interval"  value="<?php echo date(getTimeFormat(), $interval['start']); ?>-<?php echo date(getTimeFormat(), $interval['end']); ?>">
          <i class="fa fa-circle-o text-success"></i> 

          <span class="text-warning h5">From:&nbsp;<i class="fa fa-clock-o"></i></span>&nbsp;<?php echo date(getTimeFormat(), $interval['start']); ?>&nbsp;-&nbsp;<span class="text-success h5">To&nbsp;<i class="fa fa-clock-o"></i></span>&nbsp;<?php echo date(getTimeFormat(), $interval['end']); ?>
        </label>  
      </div>

      <a class="accordion-toggle p-10 hidden" style="text-decoration: none;" data-toggle="collapse" data-parent="#accordion2" href="#collapse<?php echo $key; ?>">
        
      </a>

    </div>
    <div id="collapse<?php echo $key; ?>" class="panel-collapse collapse schedule-duration-value">
      <div class="panel-body text-sm">
        <div class="col-md-2 col-xs-6">
          <strong>From:</strong>
        </div>
        <div class="col-md-3 col-xs-6">
          <input data-date-startdate="<?php echo date(getDateFormat()." ".getTimeFormat(), $interval['start']); ?>" data-date-enddate="<?php echo date(getDateFormat()." ".getTimeFormat(), $interval['end']); ?>" type="text" max="<?php echo date(getTimeFormat(), $interval['end']); ?>" min="<?php echo date(getTimeFormat(), $interval['start']); ?>" data-format="<?= getTimeFormatWithOutSeconds() ?>" data-current="<?php echo date(getDateFormat(), $interval['start']); ?>" class="form-control col-md-10 col-sm-10 col-xs-10" value="<?php echo date(getTimeFormat(), $interval['start']); ?>" placeholder="Start time" name="schedule_interval_start" disabled="disabled">

          <span class="text-danger text-sm error" style="position: absolute;left: 13px;right: -200px;bottom: 33px;"></span>
        </div>
        <div class="col-md-2 col-xs-6">
          <strong>To:</strong>
        </div>
        <div class="col-md-3 col-xs-6">
          <input data-date-startdate="<?php echo date(getDateFormat()." ".getTimeFormat(), $interval['start']); ?>" data-date-enddate="<?php echo date(getDateFormat()." ".getTimeFormat(), $interval['end']); ?>" type="text" data-format="<?= getTimeFormatWithOutSeconds() ?>" min="<?php echo date(getTimeFormat(), $interval['start']); ?>" data-current="<?php echo date("Y-m-d", $interval['end']); ?>" max="<?php echo date(getTimeFormat(), $interval['end']); ?>" class="form-control col-md-10 col-sm-10 col-xs-10" value="<?php echo date(getTimeFormat(), $interval['end']); ?>" placeholder="End time" name="schedule_interval_end" disabled="disabled">

          <span class="text-danger text-sm error" style="position: absolute;left: 13px;right: -200px;bottom: 33px;"></span>
        </div> 
        
        
      </div>
    </div>
  </div>
  <?php endforeach; ?> 
  <?php endif; ?>

</div>
<?php endif; ?>