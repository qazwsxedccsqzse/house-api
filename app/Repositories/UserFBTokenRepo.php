<?php

namespace App\Repositories;

use App\Models\UserFBToken;

class UserFBTokenRepo
{
    public function __construct(private UserFBToken $userFBToken)
    {
    }
}
