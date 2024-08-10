<?php
/**
 * File name: PaymentHelper.php
 * Last modified: 14/06/21, 7:36 PM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2021
 */

if (!function_exists('formatPrice')) {
    /**
     * Format the display price
     *
     * @param $price
     * @param $symbol
     * @param $position
     * @return string
     */
    function formatPrice($price, $symbol, $position)
    {
        return $position == 'right' ? "{$price}{$symbol}" : "{$symbol}{$price}";
    }
}
