<a href="#" class="btn btn-lg btn-danger btn-rounded filter pull-right" title="Report filter">
    <i class="fa fa-filter"></i>
</a>
<aside class="aside-lg b-l pos-abt bg-light b-a filter-container col-md-12 col-sm-12 col-xs-12" style="display: none; padding-bottom: 20px;">
    <h5 class="text-center font-bold">
        Report filter
    </h5>
    <div class="p-10 filter-body scrollable col-md-12 col-sm-12 col-xs-12">
        <div class="col-md-12 col-sm-12 col-xs-12 p-left-0 p-right-0 p-top-10 p-bottom-10">
            <label class="col-md-12 control-label">Select Estimator:</label>
            <div class="col-md-12">
                <select name="" id="filter_estimator" multiple="multiple">
                    <option value="">Empty</option>
                    <?php if(isset($estimators) && !empty($estimators)): ?>
                        <?php foreach($estimators as $estimator): ?>
                            <option value="<?php echo $estimator['id']; ?>"><?= $estimator['full_name']; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12 p-left-0 p-right-0 p-bottom-10">
            <label class="col-md-12 control-label">Select Source:</label>
            <div class="col-md-12">
                <select name="" id="filter_reference" multiple="multiple">
                    <option value="">Empty</option>
                    <?php if(isset($references) && !empty($references)): ?>
                        <?php foreach($references as $reference): ?>
                            <option value="<?php echo $reference['id']; ?>"><?= $reference['name']; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </select>
            </div>
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12 p-left-0 p-right-0">
            <label class="col-md-12 col-sm-12 col-xs-12 control-label">Select Classes:</label>
            <div class="col-md-12 col-sm-12 col-xs-12 filter-classes">
                <?php if(isset($classes) && !empty($classes)) : ?>
                    <?php foreach ($classes as $class) : ?>
                        <div class="form-check col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12">
                            <span class="pull-right">
                                <label class="switch-mini checkbox m-n">
                                    <input class="form-check-input" type="checkbox" value="" id="<?= $class['class_id'] ?>">
                                    <span></span>
                                </label>
                            </span>
                            <label class="form-check-label class-name" for="<?= $class['class_id'] ?>" title="<?= $class['class_name'] ?>">
                               <?= $class['class_name'] ?>
                            </label>
                            <div class="col-md-12 col-sm-12 col-xs-12 line line-s line-members"></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <input type="hidden" id="date-format" value="<?= getMomentJSDateFormat()?>" />
    </div>
    <div class="col-md-12 filter-go">
        <button class="btn btn-sm btn-default col-md-12 btn-go">Go!</button>
    </div>
</aside>
