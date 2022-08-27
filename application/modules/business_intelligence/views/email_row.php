<?php foreach($data[$v]['letters'] as $key => $val) : ?>
    <div class="panel panel-default">
        <div class="panel-heading col-xs-12" role="tab" id="heading_<?php echo $v . '_' . $val['email_id']; ?>"
             data-toggle="collapse" data-parent="#accordionEmails_<?php echo $v; ?>"
             href="#collapse_<?php echo $v . '_' . $val['email_id']; ?>"
             aria-expanded="false" aria-controls="collapse_<?php echo $v . '_' . $val['email_id']; ?>"
        >
            <div class="email-stat-row-date col-xs-3 no-padder text-center">
                <div>
                    <?php echo getDateTimeWithDate($val['email_created_at'], 'Y-m-d H:i:s', false); ?>
                </div>
                <div>
                    <?php echo getTimeWithDate($val['email_created_at'], 'Y-m-d H:i:s', true); ?>
                </div>
            </div>
            <div class="email-stat-row-content col-xs-8 p-sides-10" data-toggle="tooltip" data-placement="top"
                 data-original-title="<?php echo $val['email_subject'] ?>" data-container="body"
            >
                <div>
                    <?php echo $val['email_to']; ?>
                </div>
            </div>
            <div class="email-stat-row-arrow col-xs-1 no-padder">
                <i class="fa fa-chevron-down"></i>
            </div>
        </div>
        <div id="collapse_<?php echo $v . '_' . $val['email_id']; ?>" class="panel-collapse collapse col-xs-12"
             role="tabpanel" aria-labelledby="heading_<?php echo $v . '_' . $val['email_id']; ?>"
        >
            <div class="panel-body">
                <?php echo generateAdditionalInfo($val); ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
