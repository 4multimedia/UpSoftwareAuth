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
            return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
                ->withPivot('tenant_id')
                ->withTimestamps();
        } else {
            return $this->belongsToMany(Role::class);
        }
    }

    public function rolesByTenant()
    {
        $rolesByTenant = [];

        foreach ($this->roles as $role) {
            $tenantId = $role->pivot->tenant_id;
            $tenant = Tenant::find($tenantId);
            $rolesByTenant[$tenantId]["info"] = [
                "id" => $tenant->id,
                "name" => $tenant->name ?? null
            ];
            $rolesByTenant[$tenantId]["domains"] = $tenant->domains->map(fn($domain) => [
                'id' => $domain->id,
                'domain' => $domain->domain
            ]);
            $rolesByTenant[$tenantId]["roles"][] = [
                'id' => $role->id,
                'name' => $role->name,
            ];
        }

        return $rolesByTenant;
    }
}
