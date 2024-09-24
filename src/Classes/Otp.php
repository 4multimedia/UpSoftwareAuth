<?php

namespace Upsoftware\Auth\Classes;

use Carbon\Carbon;
use Upsoftware\Auth\Enums\OtpKind as Kind;
use Upsoftware\Auth\Models\Otp as OtpModel;
use Upsoftware\Auth\Models\User;
use Upsoftware\Auth\Notifications\SendOtpTokenNotify;

class Otp
{
    public \DateTime $expired_at;
    public Kind $kind;
    public ?string $email = null;
    public ?string $phone = null;
    public String $code;

    private function setExpiredAt(): \DateTime
    {
        $time = config('upsoftware.otp.time', 30);
        return $this->expired_at = (new \DateTime())->modify("+{$time} minutes");
    }

    public function setCode(string $type = null, int $length = null): string
    {
        $type = $type ?? config('upsoftware.otp.type', 'digits');
        $length = $length ?? config('upsoftware.otp.length', 10);

        $characters = match ($type) {
            'digits' => '0123456789',
            'letters' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            default => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        };

        return $this->code = substr(str_shuffle(str_repeat($characters, $length)), 0, $length);
    }

    private function calculateTime(): ?array
    {
        $retry_time = config('upsoftware.otp.retry_time', 5);
        $time = config('upsoftware.otp.time', 5);
        $expired_at = (new \DateTime());

        $otp = OtpModel::where('kind', $this->kind)
            ->where('email', $this->email)
            ->where('phone', $this->phone)
            ->where('expired_at', '>', $expired_at)
            ->first();

        if ($otp) {
            $now = time();
            $send_at = strtotime($otp->send_at) + ($retry_time * 60);
            $diff = $send_at - $now;
            $total_seconds = $diff;

            if ($total_seconds < 0) {
                return null;
            }

            $hours = floor($diff / 3600);
            $diff -= $hours * 3600;

            $minutes = floor($diff / 60);
            $diff -= $minutes * 60;

            $seconds = $diff;

            return [
                'total_seconds' => $total_seconds,
                'hours' => $hours,
                'minutes' => $minutes,
                'seconds' => $seconds
            ];
        }
        return null;
    }

    public function getTimeExpired(Kind $kind, $value) {
        $this->kind = $kind;
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->email = $value;
        } else {
            $this->phone = $value;
        }

        $validateTime = $this->calculateTime();
        if ($validateTime) {
            return $validateTime;
        } else {
            return [
                'diff' => 0,
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0,
            ];
        }
    }

    /**
     * @throws \Exception
     */
    private function checkCodeIsAlreadyGenerate(): void
    {
        $validateTime = $this->calculateTime();
        if ($validateTime) {
            $message = __('auth::otp.Next code can be generated in', [
                'minutes' => $validateTime['minutes'],
                 'seconds' => $validateTime['seconds'],
            ]);
            throw new \Exception($message);
        }
    }

    /**
     * @throws \Exception
     */
    public function createToken(Kind $kind, $value, $time = false): bool|array|null
    {
        $this->kind = $kind;
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->email = $value;
        } else {
            $this->phone = $value;
        }

        $data = [
            'kind' => $this->kind,
            'email' => $this->email,
            'phone' => $this->phone,
            'send_at' => Carbon::now(),
            'expired_at' => $this->setExpiredAt(),
            'code' => $this->setCode()
        ];

        $this->checkCodeIsAlreadyGenerate();

        try {
            if (config('upsoftware.otp.unique', false)) {
                OtpModel::updateOrCreate(
                    ['kind' => $this->kind, 'email' => $this->email, 'phone' => $this->phone],
                    $data
                );
            } else {
                OtpModel::create($data);
            }
            $this->sendToken();
            if ($time) {
                return $this->calculateTime();
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function sendToken(): void
    {
        if ($this->email) {
            $user = new User();
            $user->email = $this->email;
            $user->notify(new SendOtpTokenNotify($this->code));
        }
    }

    public function validateToken(Kind $kind, string $value, string $token): Bool {
        $this->kind = $kind;
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->email = $value;
        } else {
            $this->phone = $value;
        }

        $otp = OtpModel::where('kind', $this->kind)
            ->where('email', $this->email)
            ->where('phone', $this->phone)
            ->where('expired_at', '>', now())
            ->whereNull('used_at')
            ->where('code', $token)
            ->first();
        if ($otp) {
            $otp->update(['used_at' => now()]);
            return true;
        }

        return false;
    }
}
