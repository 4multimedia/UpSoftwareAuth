<?php

namespace Upsoftware\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Upsoftware\Auth\Contracts\Requests\RegisterUser as RegisterUserContract;

class RegisterUser extends FormRequest implements RegisterUserContract
{
    public function rules()
    {
        $rules = [
            'email'             => ['required', 'email', 'unique:users,email'],
            'password'          => ['required', 'min:8', 'max:64'],
        ];

        $additionalFields = config('upsoftware.register_additional_fields', []);

        return array_merge($rules, $additionalFields);
    }

    public function authorize()
    {
        return true;
    }
}
