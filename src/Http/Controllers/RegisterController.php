<?php

namespace Upsoftware\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Upsoftware\Auth\Contracts\Requests\RegisterUser;
use Upsoftware\Auth\Models\User;

class RegisterController extends Controller
{
    protected function userData(RegisterUser $request): array {
        return [
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ];
    }

    protected function afterRegister(User $user, RegisterUser $request) {

    }

    protected function validateAdditionalFields(RegisterUser $request) {

    }
    public function register(RegisterUser $request)
    {
        $this->validateAdditionalFields($request);
        try {
            $user = User::create($this->userData($request));
            $this->afterRegister($user, $request);
            return $user;
        } catch (\Exception $e) {
            return response(['error' => $e->getMessage()], 500);
        }
    }
}
