<?php
/**
 * File name: UpdateVideoRequest.php
 * Last modified: 10/12/21, 3:01 PM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2021
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => ['required', 'max:250'],
            'video_type' => ['required'],
            'video_link' => ['required'],
            'description' => ['nullable'],
            'thumbnail' => ['nullable'],
            'skill_id' => ['required'],
            'topic_id' => ['nullable'],
            'difficulty_level_id' => ['required'],
            'duration' => ['required'],
            'is_paid' => ['required'],
            'is_active' => ['required'],
        ];
    }
}
