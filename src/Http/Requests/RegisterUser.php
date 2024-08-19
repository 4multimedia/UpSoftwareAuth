<?php

namespace Upsoftware\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Upsoftware\Auth\Contracts\Requests\RegisterUser as RegisterUserContract;

class RegisterUser extends FormRequest implements RegisterUserContract
{
    public function rules()
    {
        return [
            'email'             => ['required', 'email', 'unique:users,email'],
            'password'          => ['required', 'min:8', 'max:64'],
        ];
    }

    public function authorize()
    {
        return true;
    }
}
