<?php
/**
 * File name: LessonRepository.php
 * Last modified: 10/07/21, 3:08 PM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2021
 */

namespace App\Repositories;

class LessonRepository
{
    /**
     * Practice Lesson Configuration Steps
     *
     * @param string $active
     * @return array[]
     */
    public function getSteps($active = 'skill')
    {
        return [
            [
                'step' => 1,
                'key' => 'skill',
                'title' => __('Choose Skill'),
                'status' => $active == 'skill' ? 'active' : 'inactive',
                'url' => route('practice.configure_lessons')
            ],
            [
                'step' => 2,
                'key' => 'lessons',
                'title' => __('Add/Remove Lessons'),
                'status' => $active == 'lessons' ? 'active' : 'inactive',
                'url' => ''
            ],
        ];
    }
}
