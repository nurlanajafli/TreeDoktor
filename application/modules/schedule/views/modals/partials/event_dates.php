
<div class="filter-dates-block" id="event-dates">
    <div class="btn btn-sm btn-primary bg-white border-bottom br-radius-0" data-toggle="click">
        <i class="glyphicon glyphicon-calendar text-primary"></i>
        <span class="date-start-view">{{:~dateFormat(event_date_start, "DD MMM, "+~getTimeFormat())}}</span>
        <?php /*<input type="text" id="event-date-start" class="no-border-input input-primary" readonly value="{{:~dateFormat(event_date_start, "DD MMM "+~getTimeFormat())}}">*/ ?>
    </div>

    <div class="btn-group" style="margin: 5px 0px 0 0;height: 32px;">
    </div>
    <div class="btn btn-sm btn-danger bg-white border-bottom br-radius-0 clockpicker-end">
        <i class="glyphicon glyphicon-calendar text-danger"></i>
        <span class="date-end-view">{{:~dateFormat(event_date_end, "DD MMM, "+~getTimeFormat())}}</span>
        <?php /*<input type="text" id="event-date-end" class="no-border-input input-danger" readonly value="{{:~dateFormat(event_date_end, "DD MMM "+~getTimeFormat())}}">*/ ?>
    </div>

    <div class="clear"></div>

</div>
