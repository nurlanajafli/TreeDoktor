<?php

namespace application\core\Auth;

use application\core\Auth\Concerns\Authenticatable;
use application\core\Auth\Concerns\Authorizable;
use application\core\Database\EloquentModel;

class User extends EloquentModel
{
    use Authenticatable, Authorizable;
}