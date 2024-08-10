<?php
/**
 * File name: SubscriptionTrait.php
 * Last modified: 02/02/22, 4:08 PM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2022
 */

namespace App\Traits;

use Illuminate\Support\Facades\Cookie;

trait SubscriptionTrait
{
    /**
     * Check user had active subscription to the category and feature
     *
     * @param $category
     * @param null $feature
     * @return bool
     */
    public function hasActiveSubscription($category, $feature = null)
    {
        // fetch the active category subscription of a user
        $subscription = $this->subscriptions()
            ->with('plan', function ($query) {
                $query->with('features');
            })
            ->where('category_id', '=', $category)
            ->where('ends_at', '>', now()->toDateTimeString())
            ->where('status', '=', 'active')
            ->first();

        if(!$subscription) {
            return false;
        }

        // feature check
        if($feature) {
            $features = $subscription->plan->features->pluck('code')->toArray();
            return in_array($feature, $features);
        } else {
            return true;
        }
    }

    /**
     * Check user had pending bank payment for a plan
     *
     * @param $planId
     * @return bool
     */
    public function hasPendingBankPayment($planId)
    {
        $pendingBankPayments = $this->payments()
            ->where('plan_id', '=', $planId)
            ->where('payment_processor', '=', 'bank')
            ->where('status', '=', 'pending')
            ->count();
        return $pendingBankPayments > 0;
    }
}
