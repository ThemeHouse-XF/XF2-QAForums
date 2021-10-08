<?php

namespace ThemeHouse\QAForums\Util;

/**
 * Class Number
 * @package ThemeHouse\QAForums\Util
 */
class Number
{
    /**
     * @param $value
     * @return string
     */
    public static function formatFriendlyNumber($value)
    {
        $prefix = '';
        if ($value < 0) {
            $prefix = '-';
            $value = abs($value);
        }
        if ($value >= 1000000) {
            return $prefix . number_format(($value / 1000000), 1) . 'M';
        }
        if ($value >= 100000) {
            return $prefix . number_format(($value / 1000), 0) . 'K';
        }

        if ($value >= 1000) {
            return $prefix . number_format(($value / 1000), 1) . 'K';
        }

        return $prefix . number_format($value);
    }
}