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

        $requestData = $request->all();
        $additionalData = [];

        foreach ($additionalFields as $key => $field) {
            if (is_array($field)) {
                $groupedData = [];
                foreach ($field as $subfield => $mappedField) {
                    // Jeśli klucz jest numerem (czyli brak mapowania), użyj wartości subfield jako klucza
                    $actualField = is_int($subfield) ? $mappedField : $subfield;

                    // Jeśli pole istnieje w request, dodaj je do groupedData
                    if ($request->has($actualField)) {
                        $groupedData[$mappedField] = $request->$actualField;
                        unset($requestData[$actualField]);
                    } else {
                        // Jeśli pole nie istnieje, ustaw je na null
                        $groupedData[$mappedField] = null;
                    }
                }
                $additionalData[$key] = $groupedData;
            } else {
                // Mapowanie pól nie-grupowych
                $mappedField = $field;
                if (is_string($key)) {
                    // Jeśli klucz jest mapowany na inne pole
                    $mappedField = $key;
                    $field = $field;
                }

                if ($request->has($field)) {
                    $additionalData[$mappedField] = $request->$field;
                    unset($requestData[$field]);
                } else {
                    $additionalData[$mappedField] = null;
                }
            }
        }

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
