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
        $config = config('upsoftware.register_fields_table', []);
        $defaultValues = config('upsoftware.register_default_values', []);

        $requestData = $request->all();
        $additionalData = [];

        // Rekurencyjna funkcja do przetwarzania pól
        $processFields = function($fields, $requestData, $defaultValues) use (&$processFields, $request) {
            $result = [];

            foreach ($fields as $key => $field) {
                if (is_array($field)) {
                    // Obsługa zagnieżdżonych tablic
                    $groupedData = [];
                    foreach ($field as $subfield => $mappedField) {
                        if (is_int($subfield)) {
                            // Przypadek, gdy klucz i wartość są takie same
                            $subfield = $mappedField;
                        }

                        // Pobieramy wartość z requesta albo z wartości domyślnych
                        $groupedData[$mappedField] = $request->input($subfield) ?? ($defaultValues[$mappedField] ?? null);
                    }
                    $result[$key] = $groupedData;
                } else {
                    // Obsługa mapowania pól nie-grupowych
                    if (is_int($key)) {
                        // Przypadek, gdy klucz i wartość są takie same (np. 'town')
                        $result[$field] = $request->input($field) ?? ($defaultValues[$field] ?? null);
                    } else {
                        // Normalne mapowanie pól (np. 'street' => 'address')
                        $result[$key] = $request->input($field) ?? ($defaultValues[$key] ?? null);
                    }
                }
            }

            return $result;
        };

        // Wykonaj funkcję przetwarzającą dodatkowe pola
        $additionalData = $processFields($config, $requestData, $defaultValues);

        // Konwertowanie pozostałych danych na JSON i dodanie do wynikowej tablicy
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
