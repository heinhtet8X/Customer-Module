<?php

namespace Modules\Customers\Http\Controllers\API;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Customers\Models\Customer;
use Modules\Customers\App\Services\AuthServices;
use Modules\Customers\Http\Requests\ResendRequest;
use Modules\Customers\Http\Requests\ForgotPasswordRequest;
use Modules\Customers\App\Processor\AuthVerificationProcessor;


class ForgotPasswordController extends Controller
{
    public function __construct(private AuthServices $authServices)
    {
        $this->authServices = $authServices;
    }

    public function send(ResendRequest $resendRequest)
    {
        try {
            $this->authServices->resend($resendRequest, Customer::STATUS_ACTIVE);
            return jsonResponse(true, 'OTP resend mail', null, 200);
        } catch (Exception $e) {
            return jsonResponse(false, $e->getMessage(), null, 400);
        }
    }

    public function update(ForgotPasswordRequest $forgotPasswordRequest)
    {
        try {
            $authVerification = new AuthVerificationProcessor();
            $this->authServices->forgotChange($authVerification, $forgotPasswordRequest);

            return jsonResponse(true, 'Forgot password change successful!', null, 200);
        } catch (Exception $e) {
            return jsonResponse(false, $e->getMessage(), null, 400);
        }
    }
}
