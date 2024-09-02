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
        // Pobranie dodatkowych pól z konfiguracji
        $additionalFields = config('upsoftware.register_fields_table', []);
        $defaultValues = config('upsoftware.register_default_values', []);

        $requestData = $request->all();
        $additionalData = [];

        // Rekurencyjna funkcja do przetwarzania pól
        $processFields = function($fields, $requestData, $defaultValues) use (&$processFields) {
            $result = [];

            foreach ($fields as $key => $field) {
                if (is_array($field)) {
                    if (array_values($field) === $field) {
                        // Jeżeli $field jest prostą tablicą
                        $groupedData = [];
                        foreach ($field as $subfield) {
                            $groupedData[$subfield] = $requestData[$subfield] ?? ($defaultValues[$subfield] ?? null);
                            unset($requestData[$subfield]);
                        }
                        $result[$key] = $groupedData;
                    } else {
                        // Jeżeli $field jest tablicą z mapowaniem
                        $groupedData = [];
                        foreach ($field as $subfield => $mappedField) {
                            $actualField = is_int($subfield) ? $mappedField : $subfield;
                            $groupedData[$mappedField] = $requestData[$actualField] ?? ($defaultValues[$mappedField] ?? null);
                            unset($requestData[$actualField]);
                        }
                        $result[$key] = $processFields($groupedData, $requestData, $defaultValues);
                    }
                } else {
                    // Mapowanie pól nie-grupowych
                    $mappedField = $field;
                    if (is_string($key)) {
                        $mappedField = $key;
                        $field = $field;
                    }

                    $result[$mappedField] = $requestData[$field] ?? ($defaultValues[$mappedField] ?? null);
                    unset($requestData[$field]);
                }
            }

            return $result;
        };

        $additionalData = $processFields($additionalFields, $requestData, $defaultValues);

        // Konwertowanie pozostałych danych na JSON i dodanie do wynikowego tablicy
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
