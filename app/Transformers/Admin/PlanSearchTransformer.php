<?php
/**
 * File name: PlanSearchTransformer.php
 * Last modified: 13/03/21, 4:04 PM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2021
 */

namespace App\Transformers\Admin;

use App\Models\Plan;
use League\Fractal\TransformerAbstract;

class PlanSearchTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @param Plan $plan
     * @return array
     */
    public function transform(Plan $plan)
    {
        return [
            'id' => $plan->id,
            'name' => $plan->full_name
        ];
    }
}
