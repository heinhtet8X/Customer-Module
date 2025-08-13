<?php

namespace Modules\Customers\Http\Controllers\API;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Customers\Http\Requests\LoginRequest;
use Modules\Customers\App\Services\AuthServices;
use Modules\Customers\Http\Requests\VerifyRequest;
use Modules\Customers\Http\Requests\RegisterRequest;
use Modules\Customers\App\Processor\AuthVerificationProcessor;
use Modules\Customers\Http\Requests\CustomerUpdateRequest;
use Modules\Customers\Http\Requests\ResendRequest;
use Modules\Customers\Models\Customer;

class AuthController extends Controller
{
    public function __construct(private AuthServices $authServices)
    {
        $this->authServices = $authServices;
    }

    public function index()
    {
        try {
            $customer = collect(Auth::user())->toArray();
            return jsonResponse(true, 'Account login successful', $customer, 200);
        } catch (Exception $e) {
            return jsonResponse(true, $e->getMessage(), null, 400);
        }
    }

    public function update(CustomerUpdateRequest $customerUpdateRequest)
    {
        try {
            $customer = $this->authServices->update($customerUpdateRequest);

            $data = array_merge(
                $customer->toArray(),
            );

            return jsonResponse(true, 'Account updated', $data, 200);
        } catch (Exception $e) {
            return jsonResponse(false, $e->getMessage(), null, 400);
        }
    }

    public function login(LoginRequest $loginRequest)
    {
        try {
            $customer = $this->authServices->login($loginRequest);
            $data     = array_merge(
                ['token' => $customer->createToken($customer->email)->plainTextToken],
                $customer->toArray(),
            );
            return jsonResponse(true, 'Account login successful', $data, 200);
        } catch (Exception $e) {
            return jsonResponse(false, $e->getMessage(), null, 400);
        }
    }

    public function register(RegisterRequest $registerRequest)
    {
        DB::beginTransaction();
        try {
            $this->authServices->register($registerRequest);
            DB::commit();
            return jsonResponse(true, 'Account created successful', null, 200);
        } catch (Exception $e) {
            DB::rollBack();
            return jsonResponse(false, $e->getMessage(), null, 400);
        }
    }

    public function verify(VerifyRequest $verifyRequest)
    {
        try {
            $authVerification = new AuthVerificationProcessor();
            $customer         = $this->authServices->verification($authVerification, $verifyRequest);

            $data = array_merge(
                ['token' => $customer->createToken($customer->email)->plainTextToken],
                $customer->toArray(),
            );

            return jsonResponse(true, 'Account verification successful', $data, 200);
        } catch (Exception $e) {
            return jsonResponse(false, $e->getMessage(), null, 400);
        }
    }

    public function resend(ResendRequest $resendRequest)
    {
        try {
            $this->authServices->resend($resendRequest, Customer::STATUS_INACTIVE);
            return jsonResponse(true, 'OTP resend mail', null, 200);
        } catch (Exception $e) {
            return jsonResponse(false, $e->getMessage(), null, 400);
        }
    }

    public function logout(Request $request)
    {
        try {
            $auth_id = Auth::user()->id;

            Customer::find($auth_id)->update([
                'last_active_at' => now(),
            ]);

            $request->user()->currentAccessToken()->delete();

            return jsonResponse(true, 'Logged out successfully', null, 200);
        } catch (Exception $e) {
            return jsonResponse(false, $e->getMessage(), null, 400);
        }
    }
}
