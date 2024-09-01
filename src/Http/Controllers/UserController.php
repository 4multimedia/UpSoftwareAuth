<?php

namespace Upsoftware\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $token = $request->bearerToken();
        return [
            'status' => 'success',
            'message' => trans('auth::messages.The user has been authorized'),
            'user' => $user,
            'token' => $token,
            'roles' => $user->roles->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name
            ]),
        ];
    }
}
