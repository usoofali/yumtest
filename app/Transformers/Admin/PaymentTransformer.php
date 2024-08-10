<?php
/**
 * File name: PaymentTransformer.php
 * Last modified: 02/02/22, 9:14 PM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2022
 */

namespace App\Transformers\Admin;

use App\Models\Payment;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PaymentTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @param Payment $payment
     * @return array
     */
    public function transform(Payment $payment)
    {
        return [
            'id' => $payment->id,
            'code' => $payment->payment_id,
            'plan' => $payment->plan->full_name,
            'user' => $payment->user->full_name,
            'amount' => $payment->total_amount.' '.$payment->currency,
            'date' => Carbon::parse($payment->payment_date)->toDayDateTimeString(),
            'invoice_no' => $payment->invoice_id,
            'method' => config('qwiktest.payment_processors')[$payment->payment_processor]['name'],
            'status' => $payment->status,
        ];
    }
}
