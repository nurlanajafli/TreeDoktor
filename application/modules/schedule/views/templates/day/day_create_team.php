<section class="panel panel-default">
    <div class="wizard clearfix">
        <ul class="steps">
            <li data-target="#step1" class="active"><span class="badge badge-info">1</span>Step 1</li>
            <li data-target="#step2" class=""><span class="badge">2</span>Step 2</li>
            <li data-target="#step3" class=""><span class="badge">3</span>Step 3</li>
        </ul>
        <div class="actions">
            <button type="button" class="btn btn-default btn-xs btn-prev no-shadow" disabled="disabled">Prev</button>
            <button class="btn btn-default btn-xs btn-next no-shadow" data-last="Finish">Next</button>
        </div>
    </div>
    <div class="step-content form-horizontal">
        <div class="step-pane active" id="step1">
            <div class="form-group">
                <label class="col-sm-4 control-label">Select Date</label>
                <div class="col-sm-8">
                    <div>
                        <input type="text" class="form-control datepicker text-center" readonly id="teamDate" value="{{:current_date}}">
                    </div>
                </div>
            </div>
            <?php $color = '#5785fa';  ?>
            <div class="form-group">
                <label class="col-sm-4 control-label">Select Crew Type</label>
                <div class="col-sm-8">
                    <div>
                        <select class="col-sm-10 form-control no-shadow" id="crewType">
                            <?php foreach($crews as $key => $crew) : ?>
                                <?php if(!$key) $color = $crew->crew_color; ?>
                                <option data-color="<?php echo $crew->crew_color; ?>" value="<?php echo $crew->crew_id; ?>"><?php echo $crew->crew_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label">Select Crew Leader</label>
                <div class="col-sm-8">
                    <div>
                        <select class="col-sm-10 form-control no-shadow" id="crewLeader"></select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label">Select Crew Color</label>
                <div class="col-sm-8">
                    <div>
                        <input type="text" class="crew_color form-control mycolorpicker"
                               readonly placeholder="Crew Color" id="crewColor"
                               value="<?php echo $color; ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="step-pane" id="step2">
            <ul class="emp-dropdown p-n" id="day-create-team-free-members"></ul>
        </div>
        <div class="step-pane" id="step3">
            <ul class="emp-dropdown p-n" id="day-create-team-free-items"></ul>
        </div>
    </div>
</section>