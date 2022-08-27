<?php if(config_item('messenger')) : ?>
    <?php
        $messages = isset($messages) && is_array($messages) ? $messages : [];
        $messages[] = (object) [
            'sms_id' => null,
            'sms_name' => 'Blank SMS',
            'sms_text' => ''
        ];
    ?>

    <div>
        <a class="btn btn-block btn-warning dropdown-toggle"
           style="margin-top: 5px; overflow: hidden;text-overflow: ellipsis;" data-toggle="dropdown">
            SMS to <?php echo $client_data->client_name; ?>
            <span class="caret" style="margin-left:5px;"></span>
        </a>

        <ul class="dropdown-menu" id="" style="max-height: 200px; overflow-y: scroll;">
            <?php foreach($messages as $key => $sms) : ?>
                <li>
                    <a href="#sms-<?php echo $sms->sms_id; ?>" data-toggle="modal" style="padding-right: 6px;padding-left: 6px;">
                        <?php echo $sms->sms_name; ?> <span class="badge bg-info"></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>