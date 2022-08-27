<?php
namespace application\modules\mail\helpers;

use application\modules\mail\models\AmazonIdentity;
use Illuminate\Database\Eloquent\Model;

class MailCheck {

    /**
     * @param $email
     * @return AmazonIdentity|Model|object|null
     */
    public function checkEmailIdentityStatus($email)
    {
        return AmazonIdentity::whereRaw("MATCH (identity) AGAINST ('\"".$email."\"' IN BOOLEAN MODE)")
            ->orderBy('identity_id', 'desc')
            ->first();
    }
}