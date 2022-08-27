<?php if(config_item('messenger')) : ?>
    <li>
        <a href="#">
            <i class="fa fa-mobile"><b class="bg-dark"></b></i>
            Send SMS
            <span class="pull-right">
                <i class="fa fa-angle-down text"></i>
                <i class="fa fa-angle-up text-active"></i>
            </span>
        </a>
        <ul class="nav lt">
            <?php if (isset($messages) && is_array($messages)): ?>
                <?php foreach ($messages as $key => $sms): ?>
                    <li>
                        <a href="#sms-<?php echo $sms->sms_id; ?>" role="button" data-toggle="modal" data-backdrop="static"
                           data-keyboard="false">
                            <i class="fa fa-angle-right"></i>
                            <?php echo $sms->sms_name; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
            <li>
                <a href="#sms-" role="button" data-toggle="modal" data-backdrop="static" data-keyboard="false">
                    <i class="fa fa-angle-right"></i>
                    Blank SMS
                </a>
            </li>
        </ul>
    </li>
<?php endif; ?>
