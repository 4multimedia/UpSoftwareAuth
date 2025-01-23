<?php

namespace Upsoftware\Auth\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Upsoftware\Auth\Models\User;

class CheckEmailController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email']
        ]);
        $status = !(User::where('email', $request->email)->count() === 0);
        return ['status' => $status];
    }
}
