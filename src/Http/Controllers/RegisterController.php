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
        $additionalFields = config('upsoftware.register_fields_table', []);

        $requestData = $request->all();

        $additionalData = [];

        foreach ($additionalFields as $key => $field) {
            if (is_array($field)) {
                $groupedData = [];
                foreach ($field as $subfield) {
                    if ($request->has($subfield)) {
                        $groupedData[$subfield] = $request->$subfield;
                        unset($requestData[$subfield]);
                    } else {
                        $groupedData[$subfield] = null;
                    }
                }
                $additionalData[$key] = $groupedData;
            } else {
                if ($request->has($field)) {
                    $additionalData[$field] = $request->$field;
                    unset($requestData[$field]);
                } else {
                    $additionalData[$field] = null;
                }
            }
        }

        $dataForColumn = json_encode($requestData);

        return array_merge([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'data' => $dataForColumn,
        ], $additionalData);
    }

    protected function afterRegister(User $user, RegisterUser $request) {
        if (is_callable(config('upsoftware.register_actions_after'))) {
            call_user_func(config('upsoftware.register_actions_after'), $user, $request);
        }
    }

    public function register(RegisterUser $request)
    {
        try {
            $user = User::create($this->userData($request));
            $this->afterRegister($user, $request);
            return $user;
        } catch (\Exception $e) {
            return response(['error' => $e->getMessage()], 500);
        }
    }
}
