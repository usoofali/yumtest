<?php
/**
 * File name: BankSettings.php
 * Last modified: 21/06/21, 11:22 AM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2021
 */

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class BankSettings extends Settings
{
    public string $bank_name;
    public string $account_owner;
    public string $account_number;
    public string $iban;
    public string $routing_number;
    public string $bic_swift;
    public string $other_details;

    public static function group(): string
    {
        return 'bank';
    }
}
