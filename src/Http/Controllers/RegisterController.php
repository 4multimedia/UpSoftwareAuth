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
                            // Przypadek, gdy klucz i wartość są takie same (np. 'town')
                            $subfield = $mappedField;
                        }

                        // Pobieramy wartość z requesta albo z wartości domyślnych
                        $groupedData[$subfield] = $request->input($mappedField) ?? ($defaultValues[$subfield] ?? null);
                    }
                    $result[$key] = $groupedData;
                } else {
                    // Obsługa mapowania pól nie-grupowych
                    if (is_int($key)) {
                        // Klucz i wartość są takie same
                        $result[$field] = $request->input($field) ?? ($defaultValues[$field] ?? null);
                    } else {
                        // Klucz mapowany na inną nazwę (np. 'name' => 'firstname')
                        $result[$key] = $request->input($field) ?? ($defaultValues[$key] ?? null);
                    }
                }
            }

            return $result;
        };

        // Przetwarzamy dodatkowe pola
        $additionalData = $processFields($config, $requestData, $defaultValues);

        // Konwertowanie pozostałych danych na JSON i dodanie do wynikowej tablicy
        $dataForColumn = json_encode($requestData);

        return array_merge([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'data' => $dataForColumn,
        ], $additionalData);
    }

    protected function beforeRegister(RegisterUser $request) {
        if (is_callable(config('upsoftware.before_register'))) {
            call_user_func(config('upsoftware.before_register'), $request);
        }
    }

    protected function afterRegister(User $user, RegisterUser $request) {
        if (is_callable(config('upsoftware.after_register'))) {
            call_user_func(config('upsoftware.after_register'), $user, $request);
        }
    }

    protected function storeUser(RegisterUser $request) {
        if (is_callable(config('upsoftware.store_register'))) {
            return call_user_func(config('upsoftware.store_register'), $request);
        }
        return null;
    }

    public function register(RegisterUser $request)
    {
        try {
            $this->beforeRegister($request);
            $user = $this->storeUser($request);
            if (!$user) {
                $user = User::create($this->userData($request));
            }
            $this->afterRegister($user, $request);
            return $user;
        } catch (\Exception $e) {
            return response(['error' => $e->getMessage()], 500);
        }
    }
}
