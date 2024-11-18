<?php

namespace Upsoftware\Auth\Models;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessTenantToken extends SanctumPersonalAccessToken
{
    protected $connection = 'central';
}
