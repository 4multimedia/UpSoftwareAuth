<?php

namespace Upsoftware\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Upsoftware\Auth\Contracts\Requests\ResetUser;
use Upsoftware\Auth\Contracts\Requests\ResetUserOtp;
use Upsoftware\Auth\Enums\OtpKind;
use Upsoftware\Auth\Models\User;

class ResetController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function reset(ResetUser $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => [trans('auth::validation.The e-mail address does not exist in our database')],
            ]);
        }

        if (config('upsoftware.otp.reset', false)) {
            return $this->handleOtpReset($user, $request->email);
        } else {

        }
    }

    /**
     * Handle OTP login flow.
     */
    private function handleOtpReset(User $user, string $email)
    {
        try {
            session(['auth_user_id' => $user->id]);
            core()->otp()->createToken(OtpKind::LOGIN, $email);
            return $this->successResponse('auth::otp.Verification code has been sent', ['step' => 2]);
        } catch (\Exception $e) {
            return $this->errorResponse('auth::otp.Verification code was not sent', 500, $e->getMessage());
        }
    }

    /**
     * Validate the OTP and login the user.
     */
    public function validate(ResetUserOtp $request)
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
            return $this->successResponse('auth.validation.Set new passwords');
        }

        return $this->errorResponse('auth::validation.Incorrect verification code');
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
