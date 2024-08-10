<?php
/**
 * File name: BillingSettings.php
 * Last modified: 21/06/21, 11:22 AM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2021
 */

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class BillingSettings extends Settings
{
    public string $vendor_name;
    public string $invoice_prefix;
    public string $address;
    public string $city;
    public string $state;
    public string $zip;
    public string $country;
    public string $phone_number;
    public string $vat_number;
    public bool $enable_invoicing;

    public static function group(): string
    {
        return 'billing';
    }
}
