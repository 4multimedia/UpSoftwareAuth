<?php

namespace Upsoftware\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Upsoftware\Auth\Contracts\Requests\LoginUser as LoginUserContract;

class LoginUser extends FormRequest implements LoginUserContract
{
    public function rules()
    {
        return [
            'email'             => ['required', 'email'],
            'password'          => ['required'],
        ];
    }

    public function authorize()
    {
        return true;
    }
}
