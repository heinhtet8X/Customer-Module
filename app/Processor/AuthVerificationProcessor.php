<?php

namespace Modules\Customers\App\Processor;

use Carbon\Carbon;
use Exception;
use Modules\Customers\App\Contracts\AuthVerificationInterface;
use Modules\Customers\Models\Customer;

class AuthVerificationProcessor implements AuthVerificationInterface
{

    public function verify(string $otp, Customer $customer): bool
    {
        $isExistOtp = $customer->otp_code === $otp;

        if (!$isExistOtp) {
            throw new Exception("OTP Code invalid!");
        }

        if (Carbon::now()->gt($customer->otp_expires_at)) {
            throw new Exception("Your OTP was expired!");
        }

        return $isExistOtp;
    }
}
