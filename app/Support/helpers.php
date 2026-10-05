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
            case 'phone':
                // Mirrors DDS.fmt.phone: 10 US digits -> (313) 555-0199, otherwise as stored.
                $raw = trim((string) $value);
                if ($raw === '') {
                    return '—';
                }
                $d = preg_replace('/\D/', '', $raw);
                if (strlen($d) === 11 && $d[0] === '1') {
                    $d = substr($d, 1);
                }

                return strlen($d) === 10
                    ? sprintf('(%s) %s-%s', substr($d, 0, 3), substr($d, 3, 3), substr($d, 6))
                    : $raw;
            case 'html':
                return $value;
            default:
                return e($value);
        }
    }
}

if (! function_exists('ops_heat_class')) {
    /**
     * Heatmap CSS class resolver for analytics tables.
     */
    function ops_heat_class(array $heat, string $key, $value): string
    {
        if ($value === null || $value === '--' || ! isset($heat[$key])) {
            return '';
        }
        $h = $heat[$key];
        $v = (float) $value;
        [$top, $bottom, $mid] = ['dds-heat-top', 'dds-heat-bottom', 'dds-heat-mid'];
        if ($h['invert']) {
            [$top, $bottom] = [$bottom, $top];
        }
        if ($v >= $h['p80']) {
            return $top;
        }
        if ($v <= $h['p20']) {
            return $bottom;
        }

        return $mid;
    }
}
