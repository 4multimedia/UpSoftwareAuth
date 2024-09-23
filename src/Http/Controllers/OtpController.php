<?php

namespace Upsoftware\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function expiryTime(Request $request) {
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

        return core()->otp()->getTimeExpired(\Upsoftware\Auth\Enums\OtpKind::REGISTER, $request->email);
    }
}
