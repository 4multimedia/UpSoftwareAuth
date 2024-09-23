<?php

namespace Upsoftware\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function expiryTime(Request $request) {
        $request->validate([
            'email' => ['required', 'email'],
            'kinb' => ['required']
        ]);
    }
}
