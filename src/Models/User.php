<?php

namespace Upsoftware\Auth\Models;

use App\Models\User as UserBaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Upsoftware\Core\Models\Tenant;

class User extends UserBaseModel
{
    use HasApiTokens;

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'role_user', 'user_id', 'tenant_id')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function getFillable()
    {
        $additionalFields = collect(config('upsoftware.register_fields_table'))
            ->flatten()
            ->toArray();

        $defaultFields = ['data', 'status'];

        return array_merge(parent::getFillable(), $defaultFields, $additionalFields);
    }

    protected function casts(): array
    {
        $additionalCasts = config('upsoftware_auth.casts', []);

        $additionalFields = config('upsoftware_auth.additional_fields', []);
        $castsForArrays = [];

        foreach ($additionalFields as $key => $field) {
            if (is_array($field)) {
                $castsForArrays[$key] = 'json';
            }
        }

        $defaultCasts = ['data' => 'json'];

        return array_merge(parent::casts(), $additionalCasts, $castsForArrays, $defaultCasts);
    }

    public function roles() {
        if (config('upsoftware.tenancy')) {
            return $this->belongsToMany(Tenant::class, 'role_user', 'role_id', 'tenant_id')
                ->withPivot('user_id');
        } else {
            return $this->belongsToMany(Role::class);
        }
    }
}
