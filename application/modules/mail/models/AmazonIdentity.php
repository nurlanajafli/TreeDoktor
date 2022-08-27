<?php

namespace application\modules\mail\models;

use application\core\Database\EloquentModel;
use application\modules\dashboard\models\traits\FullTextSearch;

class AmazonIdentity extends EloquentModel
{
    use FullTextSearch;

    protected $table = 'amazon_identities';
    protected $primaryKey = 'identity_id';

    protected $fillable = [
        'identity',
        'is_domain'
    ];
}
