<?php

namespace Upsoftware\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Upsoftware\Auth\Contracts\Requests\LoginUserOtp as LoginUserOtpContract;
use Upsoftware\Auth\Validators\OtpValidator;

class LoginUserOtp extends FormRequest implements LoginUserOtpContract
{
    public function rules()
    {
        return [
            'email'             => ['required', 'email'],
            'code'              => OtpValidator::getCodeRules()
        ];
    }

    public function authorize()
    {
        return true;
    }
}
