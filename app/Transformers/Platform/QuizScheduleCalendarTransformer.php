<?php
/**
 * File name: QuizScheduleCalendarTransformer.php
 * Last modified: 17/07/21, 3:39 PM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2021
 */

namespace App\Transformers\Platform;

use App\Models\QuizSchedule;
use League\Fractal\TransformerAbstract;

class QuizScheduleCalendarTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @param QuizSchedule $schedule
     * @return array
     */
    public function transform(QuizSchedule $schedule)
    {
        return [
            'key' => $schedule->code,
            'highlight' => $schedule->schedule_type == 'fixed' ?  ['color' => 'blue', 'fillMode' => 'light'] : ['color' => 'green', 'fillMode' => 'light'],
            'dot' => $schedule->schedule_type == 'fixed' ?  'blue' : 'green',
            'dates' => $schedule->schedule_type == 'fixed' ?  $schedule->starts_at : ['start' => $schedule->starts_at, 'end' => $schedule->ends_at],
            'popover' => [
                'visibility' => 'focus'
            ],
            'customData' => [
                'code' => $schedule->code,
                'slug' => $schedule->quiz->slug,
                'title' => $schedule->quiz->title,
                'type' => ucfirst($schedule->schedule_type)
            ]
        ];
    }
}
