<?php

namespace Upsoftware\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Role extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['name', 'description'];

    public $guarded = [];

    public function users() {
        return $this->belongsToMany(User::class);
    }
}
