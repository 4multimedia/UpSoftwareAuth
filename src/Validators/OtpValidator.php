<?php

namespace Upsoftware\Auth\Validators;

class OtpValidator
{
    public static function getCodeRules(): array
    {
        $otpConfig = config('upsoftware.otp');

        $type = $otpConfig['type'] ?? 'digits';
        $length = $otpConfig['length'] ?? 6;

        $codeRules = [
            'required',
        ];

        switch ($type) {
            case 'digits':
                $codeRules[] = "numeric";
                $codeRules[] = "digits:$length";
                break;
            case 'letters':
                $codeRules[] = "string";
                $codeRules[] = "alpha";
                $codeRules[] = "size:$length";
                break;
            case 'alphanumeric':
                $codeRules[] = "string";
                $codeRules[] = "alpha_num";
                $codeRules[] = "size:$length";
                break;
            default:
                throw new \InvalidArgumentException("Invalid OTP type: $type");
        }

        return $codeRules;
    }
}
