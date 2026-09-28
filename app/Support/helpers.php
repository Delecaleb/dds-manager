<?php

/*
 * Global view helpers, autoloaded by Composer ("files"). Keep this file small: only
 * formatting that every server-rendered view shares.
 */

if (! function_exists('ops_fmt')) {
    /**
     * The ONE server-side formatter for money / percent / number cells. Mirrors DDS.fmt in
     * public/js/ui.js so client and server render the same text. Null renders as an em dash.
     */
    function ops_fmt($value, string $type): string
    {
        if ($value === null) {
            return '—';
        }
        if ($value === '--') {
            return '--';
        }
        switch ($type) {
            case 'money':
                $v = (float) $value;
                if ($v == 0) {
                    return '$ 0';
                }
                $abs = number_format(abs($v), 2);

                return $v < 0 ? "$ ($abs)" : "$ $abs";
            case 'percent':
                return number_format((float) $value, 2).'%';
            case 'percent_0':
                return number_format((float) $value).'%';
            case 'number_3':
                return number_format((float) $value, 3);
            case 'number_2':
                return number_format((float) $value, 2);
            case 'number':
                $v = (float) $value;

                return floor($v) == $v ? number_format($v) : number_format($v, 2);
            case 'html':
                return $value;
            default:
                return e($value);
        }
    }
}
