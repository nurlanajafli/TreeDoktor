<?php

use application\modules\billing\models\SmsOrder;
use application\modules\billing\models\SmsSubscription;
use Illuminate\Support\Carbon;

class renewal_on_period extends CI_Driver implements JobsInterface
{
    var $payload;

    public function getPayload($data = null)
    {
        if (empty($data['sub_id']) || empty($data['sub_next_date']) || empty($data['sub_period'])) {
            return false;
        }

        return $data;
    }

    function execute($job)
    {
        $this->payload = json_decode($job->job_payload);
        $subscription = SmsSubscription::getActiveSubscription($this->payload->sub_id);
        $today = Carbon::today();

        if (!$subscription || !$subscription->next_date || !$today->eq(new Carbon($subscription->next_date))) {
            return false;
        }

        return SmsOrder::renewalOnPeriod($subscription, $this->payload);
    }
}
