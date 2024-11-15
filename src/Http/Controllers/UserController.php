<?php

namespace Upsoftware\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Upsoftware\Auth\Http\Resources\UserResource;

class UserController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $token = $request->bearerToken();

        $resorce = config('upsoftware.user.resource', UserResource::class);

        return [
            'status' => 'success',
            'message' => trans('auth::messages.The user has been authorized'),
            'user' => new $resorce($user),
            'token' => $token
        ];
    }
}
