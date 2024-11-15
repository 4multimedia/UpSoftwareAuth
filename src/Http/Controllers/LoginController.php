<?php

namespace Upsoftware\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Upsoftware\Auth\Contracts\Requests\LoginUser;
use Upsoftware\Auth\Contracts\Requests\LoginUserOtp;
use Upsoftware\Auth\Enums\OtpKind;
use Upsoftware\Auth\Http\Resources\UserResource;
use Upsoftware\Auth\Models\User;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function login(LoginUser $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth::validation.Incorrect email address or password')],
            ]);
        }

        if (config('upsoftware.otp.login', false)) {
            return $this->handleOtpLogin($user, $request->email);
        }

        return $this->generateAuthTokenResponse($user);
    }

    /**
     * Validate the OTP and login the user.
     */
    public function validate(LoginUserOtp $request)
    {
        $authUserId = $request->session()->get('auth_user_id');
        if (!$authUserId) {
            return $this->errorResponse('auth::validation.The login session has not been initialized');
        }

        $user = User::where('email', $request->email)->first();
        if (!$user || ($authUserId !== $user->id)) {
            return $this->errorResponse('auth::validation.Incorrect user session');
        }

        if (core()->otp()->validateToken(OtpKind::LOGIN, $request->email, $request->code)) {
            return $this->generateAuthTokenResponse($user);
        }

        return $this->errorResponse('auth::validation.Incorrect verification code');
    }

    /**
     * Handle OTP login flow.
     */
    private function handleOtpLogin(User $user, string $email)
    {
        try {
            session(['auth_user_id' => $user->id]);
            core()->otp()->createToken(OtpKind::LOGIN, $email);
            return $this->successResponse('auth::otp.Verification code has been sent');
        } catch (\Exception $e) {
            return $this->errorResponse('auth::otp.Verification code was not sent', 500, $e->getMessage());
        }
    }

    /**
     * Generate the response with the auth token.
     */
    private function generateAuthTokenResponse(User $user)
    {
        $token = $user->createToken('auth_token')->plainTextToken;
        $resorce = config('upsoftware.user.resource', UserResource::class);

        return [
            'status' => 'success',
            'message' => trans('auth::messages.The user has been logged in'),
            'user' => new $resorce($user),
            'token' => $token,
        ];
    }

    /**
     * Generate a success response.
     */
    private function successResponse(string $message, array $data = [])
    {
        return array_merge([
            'status' => 'success',
            'message' => trans($message),
        ], $data);
    }

    /**
     * Generate an error response.
     */
    private function errorResponse(string $message, int $status = 500, string $errorDetail = null)
    {
        $response = [
            'status' => 'error',
            'message' => trans($message),
        ];

        if ($errorDetail) {
            $response['error'] = $errorDetail;
        }

        return response($response, $status);
    }
}
