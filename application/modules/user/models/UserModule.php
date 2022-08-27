<?php

namespace application\modules\user\models;

use application\core\Database\EloquentModel;

class UserModule extends EloquentModel
{
    protected $table = 'user_module';
    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'module_id', 'module_status'];

    /**
     * Get the user that owns the meta.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}