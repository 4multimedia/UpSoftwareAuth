<?php

namespace Upsoftware\Auth\Models;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessTenantToken extends SanctumPersonalAccessToken
{
    protected $connection = 'central';
    protected $table = 'personal_access_tokens';
}
