<?php
/**
 * File name: QuizTypeFilters.php
 * Last modified: 11/05/21, 3:32 PM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2021
 */

namespace App\Filters;

class QuizTypeFilters extends QueryFilter
{
    /*
    |--------------------------------------------------------------------------
    | DEFINE ALL COLUMN FILTERS BELOW
    |--------------------------------------------------------------------------
    */

    public function name($query)
    {
        return $this->builder->where('name', 'like', '%'.$query.'%');
    }

    public function code($query)
    {
        return $this->builder->where('code', 'like', '%'.$query.'%');
    }

    public function status($query = null)
    {
        return $this->builder->where('is_active', $query);
    }
}

