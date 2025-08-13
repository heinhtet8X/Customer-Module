<?php

namespace Modules\Customers\App\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Modules\Customers\App\Contracts\AuthVerificationInterface;
use Modules\Customers\Models\Customer;
use Modules\Customers\Emails\OTPVerificationMail;

class AuthServices
{

    public function login(Request $request)
    {
        $customer = Customer::where(function ($query) use ($request) {
            $query->where('email', $request->credential)
                ->orWhere('phone', $request->credential);
        })
            ->first();


        if (is_null($customer)) {
            throw new Exception("Account doesn't exists!");
        }

        $checkPwd = Hash::check($request->password, $customer->password);

        if ($checkPwd) {
            return $customer;
        } else {
            throw new Exception("Wrong password, please try again!");
        }
    }

    public function register(Request $request)
    {
        $existing = Customer::where(function ($query) use ($request) {
            $query->where('username', $request->username)
                ->orWhere('email', $request->email);
        })
            ->first();

        if ($existing) {
            if ($existing->username === $request->username) {
                throw new Exception('Username already taken!');
            }
            if ($existing->email === $request->email) {
                throw new Exception('Email already taken!');
            }
        }

        $otp = rand(100000, 999999);

        $customer = Customer::create([
            'username'       => $request->username,
            'first_name'     => $request->first_name,
            'last_name'      => $request->last_name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'otp_code'       => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // Send OTP email
        Mail::to($customer->email)->send(new OTPVerificationMail($customer));
    }

    public function update(Request $request)
    {
        $auth_id  = Auth::user()->id;
        $customer = Customer::find($auth_id);

        if (is_null($customer)) {
            throw new Exception("Your account is not found!");
        }

        $isExistUsername = Customer::where('username', $request->username)
            ->whereNot('id', $auth_id)
            ->exists();

        $isExistEmail = Customer::where('email', $request->email)
            ->whereNot('id', $auth_id)
            ->exists();

        $isExistPhone = Customer::where('phone', $request->phone)
            ->whereNot('id', $auth_id)
            ->exists();

        if ($isExistUsername) {
            throw new Exception("Username already taken!");
        }

        if ($isExistEmail) {
            throw new Exception("Email already exists!");
        }

        if ($isExistPhone) {
            throw new Exception("Phone number already exists!");
        }

        $customer->update([
            'username'   => $request->username,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'dial_code'  => $request->dial_code,
            'phone'      => $request->phone,
        ]);

        if ($request->filled('password')) {
            $customer->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return $customer;
    }

    public function verification(AuthVerificationInterface $authVerificationInterface, Request $request)
    {
        $customer = Customer::where(function ($query) use ($request) {
            $query->where('email', $request->credential)
                ->orWhere('phone', $request->credential);
        })
            ->first();

        if (is_null($customer)) {
            throw new Exception("Account doesn't exists!");
        }

        $isVerify = $authVerificationInterface->verify($request->otp, $customer);

        if ($isVerify) {
            $customer->update([
                'status'         => "0",
                'otp_code'       => null,
                'otp_expires_at' => null,
            ]);
            return $customer;
        }
    }

    public function resend(Request $request, string $status)
    {
        $customer = Customer::where('status', $status)
            ->where(function ($query) use ($request) {
                $query->where('email', $request->credential)
                    ->orWhere('phone', $request->credential);
            })
            ->first();

        if (is_null($customer)) {
            throw new Exception("Account doesn't exists!");
        }

        $otp = rand(100000, 999999);

        $customer->update([
            'otp_code'       => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        Mail::to($customer->email)->send(new OTPVerificationMail($customer));
    }

    public function forgotChange(AuthVerificationInterface $authVerificationInterface, Request $request)
    {
        $customer = Customer::isActive()
            ->where(function ($query) use ($request) {
                $query->where('email', $request->credential)
                    ->orWhere('phone', $request->credential);
            })
            ->first();

        if (is_null($customer)) {
            throw new Exception("Account doesn't exists!");
        }

        $isVerify = $authVerificationInterface->verify($request->otp, $customer);

        if ($isVerify) {
            $customer->update([
                'password'       => Hash::make($request->password),
                'otp_code'       => null,
                'otp_expires_at' => null,
            ]);
            return $customer;
        }
    }
}
