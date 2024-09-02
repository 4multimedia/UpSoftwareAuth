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

        // Zbiór wszystkich kluczy z requestu
        $requestData = $request->all();

        // Inicjalizacja tablicy na dodatkowe dane użytkownika
        $additionalData = [];
        $dataForColumn = []; // Dane, które trafią do kolumny 'data'

        // Iteracja po konfiguracji dodatkowych pól
        foreach ($additionalFields as $key => $field) {
            if (is_array($field)) {
                // Jeśli pole jest tablicą (np. company), grupujemy je w JSON
                $groupedData = [];
                foreach ($field as $subfield) {
                    if ($request->has($subfield)) {
                        $groupedData[$subfield] = $request->$subfield;
                        // Usuwamy z requestData, ponieważ pole ma pokrycie
                        unset($requestData[$subfield]);
                    }
                }
                // Zapisujemy dane jako JSON w kluczu głównym (np. company)
                $additionalData[$key] = json_encode($groupedData);
            } else {
                // Jeśli pole nie jest tablicą, zapisujemy je bezpośrednio
                if ($request->has($field)) {
                    $additionalData[$field] = $request->$field;
                    // Usuwamy z requestData, ponieważ pole ma pokrycie
                    unset($requestData[$field]);
                }
            }
        }

        // Reszta danych, które nie miały pokrycia, trafi do kolumny 'data'
        $dataForColumn = json_encode($requestData);

        // Łączenie danych użytkownika z dodatkowymi polami
        return array_merge([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'data' => $dataForColumn,  // Zapisanie pozostałych danych w kolumnie 'data'
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
