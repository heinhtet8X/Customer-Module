<?php

namespace Modules\Customers\App\Contracts;

use Modules\Customers\Models\Customer;

interface AuthVerificationInterface
{
    public function verify(string $otp, Customer $customer): bool;
}
