<?php

namespace Upsoftware\Auth\Models;

use App\Models\User as UserBaseModel;
use Laravel\Sanctum\HasApiTokens;

class User extends UserBaseModel
{
    use HasApiTokens;

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
        // Pobranie dodatkowych rzutowań z konfiguracji
        $additionalCasts = config('upsoftware_auth.casts', []);

        // Iterowanie przez additional_fields i dodanie rzutowania na JSON dla tablic
        $additionalFields = config('upsoftware_auth.additional_fields', []);
        $castsForArrays = [];

        foreach ($additionalFields as $key => $field) {
            if (is_array($field)) {
                // Jeśli pole jest tablicą (np. company), rzutuj je jako JSON
                $castsForArrays[$key] = 'json';
            }
        }

        $defaultCasts = ['data' => 'json'];

        // Połączenie rzutowań z bazowego modelu z dodatkowymi rzutowaniami i tymi z tablic
        return array_merge(parent::casts(), $additionalCasts, $castsForArrays, $defaultCasts);
    }

    public function roles() {
        return $this->belongsToMany(Role::class);
    }
}
