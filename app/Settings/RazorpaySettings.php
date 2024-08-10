<?php
/**
 * File name: BankSettings.php
 * Last modified: 21/06/21, 11:22 AM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2021
 */

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class RazorpaySettings extends Settings
{
    public string $key_id;
    public string $key_secret;
    public string $webhook_url;
    public string $webhook_secret;

    public static function group(): string
    {
        return 'razorpay';
    }
}
