<?php
/**
 * File name: VideoRepository.php
 * Last modified: 10/07/21, 3:08 PM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2021
 */

namespace App\Repositories;

class VideoRepository
{
    /**
     * Practice Video Configuration Steps
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
                'url' => route('practice.configure_videos')
            ],
            [
                'step' => 2,
                'key' => 'videos',
                'title' => __('Add/Remove Videos'),
                'status' => $active == 'videos' ? 'active' : 'inactive',
                'url' => ''
            ],
        ];
    }
}
