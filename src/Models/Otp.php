<?php

namespace Upsoftware\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    public $fillable = [
        'expired_at',
        'kind',
        'used_at',
        'kind',
        'email',
        'phone',
        'code'
    ];
}
