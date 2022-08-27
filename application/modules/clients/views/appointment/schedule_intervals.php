<?php /*if(isset($intervals) && count($intervals)): ?>
<div class="panel-group m-b" id="accordion2">
  <?php if(isset($task_author_id)): ?>

    <div class="m-b-sm">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-sm btn-info">
          <input type="radio" name="options" id="option1"><i class="fa fa-check text-active"></i> Male
        </label>
        <label class="btn btn-sm btn-success">
          <input type="radio" name="options" id="option2"><i class="fa fa-check text-active"></i> Female
        </label>
        <label class="btn btn-sm btn-primary active">
          <input type="radio" name="options" id="option3"><i class="fa fa-check text-active"></i> N/A
        </label>
      </div>
    </div>

  <?php foreach($intervals as $key=>$interval): ?>                  

  <div class="panel panel-default schedule-interval-container">
    <div class="panel-heading text-center p-n ">
      
      <div class="radio m-n p-top-10 p-bottom-10">
        <label class="pull-left">Free time:</label>
        <label class="radio-custom">
          <input type="radio" name="time_interval"  value="<?php echo date('H:i', $interval['start']); ?>-<?php echo date('H:i', $interval['end']); ?>">
          <i class="fa fa-circle-o text-success"></i> 

          <span class="text-warning h5">From:&nbsp;<i class="fa fa-clock-o"></i></span>&nbsp;<?php echo date("H:i", $interval['start']); ?>&nbsp;-&nbsp;<span class="text-success h5">To&nbsp;<i class="fa fa-clock-o"></i></span>&nbsp;<?php echo date("H:i", $interval['end']); ?>
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
          <input data-date-startdate="<?php echo date("Y-m-d
 ", $interval['start']); ?>" data-date-enddate="<?php echo date("Y-m-d H:i", $interval['end']); ?>" type="text" max="<?php echo date("H:i", $interval['end']); ?>" min="<?php echo date("H:i", $interval['start']); ?>" data-format="hh:mm" data-current="<?php echo date("Y-m-d", $interval['start']); ?>" class="form-control col-md-10 col-sm-10 col-xs-10" value="<?php echo date("H:i", $interval['start']); ?>" placeholder="Start time" name="schedule_interval_start" disabled="disabled">

          <span class="text-danger text-sm error" style="position: absolute;left: 13px;right: -200px;bottom: 33px;"></span>
        </div>
        <div class="col-md-2 col-xs-6">
          <strong>To:</strong>
        </div>
        <div class="col-md-3 col-xs-6">
          <input data-date-startdate="<?php echo date("Y-m-d H:i", $interval['start']); ?>" data-date-enddate="<?php echo date("Y-m-d H:i", $interval['end']); ?>" type="text" data-format="hh:mm" min="<?php echo date("H:i", $interval['start']); ?>" data-current="<?php echo date("Y-m-d", $interval['end']); ?>" max="<?php echo date("H:i", $interval['end']); ?>" class="form-control col-md-10 col-sm-10 col-xs-10" value="<?php echo date("H:i", $interval['end']); ?>" placeholder="End time" name="schedule_interval_end" disabled="disabled">

          <span class="text-danger text-sm error" style="position: absolute;left: 13px;right: -200px;bottom: 33px;"></span>
        </div> 
        
        
      </div>
    </div>
  </div>
  <?php endforeach; ?> 
  <?php endif; ?>

</div>
<?php endif;*/ ?>

<?php if(isset($intervals) && !empty($intervals)): ?>
<div class="panel-group m-b" id="accordion2">
  <?php if(isset($task_author_id)): ?>

  <div class="m-b-sm">                
  <div class="btn-group" data-toggle="buttons">
  <?php foreach($intervals as $key=>$interval): ?>
    <?php $int = 45*60; ?>
    
    <?php for($i=(int)$interval['start'];  $i<(int)$interval['end']; $i=($i+$int)): ?>
    
        
      <label class="btn btn-sm btn-success  m-right-5 m-bottom-5">
        <input type="radio" name="time_interval" id="option2"><i class="fa fa fa-clock-o text-active"></i>
        <?php echo date(getTimeFormat(), $i); ?>&nbsp;-&nbsp;<?php echo date(getTimeFormat(), $i+$int); ?>
      </label>
      
      <div class="schedule-duration-value hidden">

        <input data-date-startdate="<?php echo date(getDateFormat().' '.getTimeFormat(), $i); ?>" data-date-enddate="<?php echo date(getDateFormat().' '.getTimeFormat(), $i+$int); ?>" type="hidden" data-format="hh:mm" data-current="<?php echo date(getDateFormat(), $i); ?>" value="<?php echo date(getTimeFormat(), $i); ?>" name="schedule_interval_start" disabled="disabled">

        <input data-date-startdate="<?php echo date(getDateFormat().' '.getTimeFormat(), $i); ?>" data-date-enddate="<?php echo date(getDateFormat().' '.getTimeFormat(), $i+$int); ?>" type="hidden" data-format="hh:mm" data-current="<?php echo date(getDateFormat(), $i+$int); ?>" value="<?php echo date(getTimeFormat(), $i+$int); ?>" name="schedule_interval_end" disabled="disabled">

      </div>

    
  <?php endfor; ?>
  
  <?php /*
  <div class="panel panel-default schedule-interval-container">
    <div class="panel-heading text-center p-n ">
      
      <div class="radio m-n p-top-10 p-bottom-10">
        <label class="pull-left">Free time:</label>
        <label class="radio-custom">
          <input type="radio" name="time_interval"  value="<?php echo date('H:i', $interval['start']); ?>-<?php echo date('H:i', $interval['end']); ?>">
          <i class="fa fa-circle-o text-success"></i> 

          <span class="text-warning h5">From:&nbsp;<i class="fa fa-clock-o"></i></span>&nbsp;<?php echo date("H:i", $interval['start']); ?>&nbsp;-&nbsp;<span class="text-success h5">To&nbsp;<i class="fa fa-clock-o"></i></span>&nbsp;<?php echo date("H:i", $interval['end']); ?>
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
          <input data-date-startdate="<?php echo date("Y-m-d H:i", $interval['start']); ?>" data-date-enddate="<?php echo date("Y-m-d H:i", $interval['end']); ?>" type="text" max="<?php echo date("H:i", $interval['end']); ?>" min="<?php echo date("H:i", $interval['start']); ?>" data-format="hh:mm" data-current="<?php echo date("Y-m-d", $interval['start']); ?>" class="form-control col-md-10 col-sm-10 col-xs-10" value="<?php echo date("H:i", $interval['start']); ?>" placeholder="Start time" name="schedule_interval_start" disabled="disabled">

          <span class="text-danger text-sm error" style="position: absolute;left: 13px;right: -200px;bottom: 33px;"></span>
        </div>
        <div class="col-md-2 col-xs-6">
          <strong>To:</strong>
        </div>
        <div class="col-md-3 col-xs-6">
          <input data-date-startdate="<?php echo date("Y-m-d H:i", $interval['start']); ?>" data-date-enddate="<?php echo date("Y-m-d H:i", $interval['end']); ?>" type="text" data-format="hh:mm" min="<?php echo date("H:i", $interval['start']); ?>" data-current="<?php echo date("Y-m-d", $interval['end']); ?>" max="<?php echo date("H:i", $interval['end']); ?>" class="form-control col-md-10 col-sm-10 col-xs-10" value="<?php echo date("H:i", $interval['end']); ?>" placeholder="End time" name="schedule_interval_end" disabled="disabled">

          <span class="text-danger text-sm error" style="position: absolute;left: 13px;right: -200px;bottom: 33px;"></span>
        </div> 
        
        
      </div>
    </div>
  </div>*/ ?>
  <?php endforeach; ?> 
  </div>
  </div>  

  <?php endif; ?>

</div>
<?php endif; ?>