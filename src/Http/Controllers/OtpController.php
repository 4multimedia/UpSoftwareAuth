<?php

namespace Upsoftware\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    protected function validateRequest(Request $request): void
    {
        $request->validate([
            'email' => [
                'nullable',
                'email',
                function ($attribute, $value, $fail) use ($request) {
                    if (empty($request->email) && empty($request->phone)) {
                        $fail('Pole email lub telefon jest wymagane.');
                    }
                }
            ],
            'phone' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    if (empty($request->email) && empty($request->phone)) {
                        $fail('Pole email lub telefon jest wymagane.');
                    }
                }
            ],
            'kind' => ['required']
        ]);
    }

    public function expiryTime(Request $request): bool
    {
        $this->validateRequest($request);
        return $this->createToken($request);
    }

    // Akcja odnawiająca OTP
    public function renew(Request $request): bool
    {
        $this->validateRequest($request);
        return $this->createToken($request);
    }


    protected function createToken(Request $request): bool
    {
        return core()->otp()->createToken(\Upsoftware\Auth\Enums\OtpKind::REGISTER, $request->email);
    }
}
