<?php
/**
 * File name: PaymentFilters.php
 * Last modified: 02/02/22, 9:14 PM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2022
 */

namespace App\Filters;


class PaymentFilters extends QueryFilter
{
    public function method($query = "")
    {
        return $this->builder->where('payment_processor', 'like', '%'.$query.'%');
    }

    public function code($query = "")
    {
        return $this->builder->where('payment_id', 'like', '%'.$query.'%');
    }

    public function status($status = null)
    {
        return $this->builder->where('status', $status);
    }
}
