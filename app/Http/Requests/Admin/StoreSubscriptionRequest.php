<?php
/**
 * File name: StoreSubscriptionRequest.php
 * Last modified: 04/02/22, 3:34 PM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2022
 */

namespace App\Http\Requests\Admin;


use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
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
            'user_id' => ['required'],
            'plan_id' => ['required'],
            'status' => ['required', 'string'],
        ];
    }

}
