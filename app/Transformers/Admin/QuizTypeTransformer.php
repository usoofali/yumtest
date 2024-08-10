<?php
/**
 * File name: SectionTransformer.php
 * Last modified: 11/05/21, 5:48 PM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2021
 */

namespace App\Transformers\Admin;

use App\Models\QuizType;
use League\Fractal\TransformerAbstract;

class QuizTypeTransformer extends TransformerAbstract
{
    public function transform(QuizType $quizType)
    {
        return [
            'id' => $quizType->id,
            'name' => $quizType->name,
            'code' => $quizType->code,
            'status' => $quizType->is_active,
        ];
    }
}

