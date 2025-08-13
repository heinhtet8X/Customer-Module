<?php

namespace Modules\Customers\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Customers\Models\Customer;

class OTPVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('Your OTP Verification Code')
            ->view('customers::mails.otp')
            ->with(['customer' => $this->customer]);
    }
}
