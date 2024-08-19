<?php

namespace Upsoftware\Auth\Models;

use App\Models\User as UserBaseModel;
use Laravel\Sanctum\HasApiTokens;

class User extends UserBaseModel
{
    use HasApiTokens;

    public function roles() {
        return $this->belongsToMany(Role::class);
    }
}
