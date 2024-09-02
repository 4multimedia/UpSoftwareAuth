<?php

namespace Upsoftware\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Upsoftware\Auth\Contracts\Requests\RegisterUser;
use Upsoftware\Auth\Models\User;

class RegisterController extends Controller
{
    protected function userData(RegisterUser $request): array {
        // Pobranie dodatkowych danych z konfiguracji
        $additionalData = collect(config('upsoftware.register_additional_fields', []))
            ->mapWithKeys(function ($rule, $field) use ($request) {
                return [$field => $request->$field];
            })->toArray();

        // Połączenie danych użytkownika z dodatkowymi polami
        return array_merge([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ], $additionalData);
    }

    protected function afterRegister(User $user, RegisterUser $request) {
        if (is_callable(config('upsoftware.register_actions_after'))) {
            call_user_func(config('upsoftware.register_actions_after'), $user, $request);
        }
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
