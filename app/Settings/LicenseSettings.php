<?php
/**
 * File name: LocalizationSettings.php
 * Last modified: 21/06/21, 11:22 AM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2021
 */

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class LicenseSettings extends Settings
{
    public string $purchase_code;
    public string $activation_key;

    public static function group(): string
    {
        return 'license';
    }
}
