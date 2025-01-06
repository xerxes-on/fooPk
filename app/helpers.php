<?php

declare(strict_types=1);

if (!function_exists('truncate_tables')) {
    function truncate_tables(array $tables): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($tables as $tableName) {
            DB::table($tableName)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

if (!function_exists('get_memory_usage')) {
    /**
     * Get usage and peak memory usage.
     */
    function get_memory_usage(): array
    {
        return [
            'usage' => round(memory_get_usage() / 1024),
            'peak'  => round(memory_get_peak_usage() / 1024)
        ];
    }
}

if (!function_exists('trans_fb')) {
    /**
     * Makes translation fall back to specified value if definition does not exist
     */
    function trans_fb(string $key, ?string $fallback = '', ?string $locale = null, ?array $replace = []): array|string|\Illuminate\Contracts\Translation\Translator|null
    {
        return app('translator')->has($key, $locale) ? trans($key, $replace, $locale) : $fallback;
    }
}

if (!function_exists('send_raw_admin_email')) {
    /**
     * Send raw admin notification email.
     * @param string $textEmail Content of the email
     * @param string $subject Subject of the email
     */
    function send_raw_admin_email(string $textEmail, string $subject): void
    {
        \Mail::raw(
            $textEmail,
            static function ($message) use ($subject) {
                $message->from(config('mail.from.address'), config('mail.from.name'))
                    ->to(config('mail.from.address'))
                    ->subject($subject);
            }
        );
    }
}

if (!function_exists('parseDateString')) {
    /**
     * parse date string into convenient way.
     */
    function parseDateString(string $dateString, string $format = 'Y-m-d'): string
    {
        try {
            return \Carbon\Carbon::parse($dateString)->format($format);
        } catch (\Throwable $e) {
            logError($e);
            return '';
        }
    }
}

if (!function_exists('logError')) {
    /**
     * Log error message with trace obtained from Throwable.
     */
    function logError(\Throwable $error, ?array $context = null): void
    {
        $trace = ['trace' => $error->getTraceAsString()];
        \Log::error($error->getMessage(), is_array($context) ? array_merge($context, $trace) : $trace);
    }
}

if (!function_exists('debugSql')) {
    /**
     * Return formatted SQL with bindings for debugging.
     */
    function debugSql(mixed $builder): string
    {
        $query = str_replace(array('?'), array('\'%s\''), $builder->toSql());
        return vsprintf($query, $builder->getBindings());
    }
}

if (!function_exists('shortenNumbers')) {
    /**
     * Format numbers to shorten format.
     */
    function shortenNumbers(int $number): int|string
    {
        $suffix  = '';
        $nFormat = '';
        if ($number >= 0 && $number < 1000) {
            // 1 - 999
            $nFormat = floor($number);
        } elseif ($number >= 1000 && $number < 1000000) {
            // 1k-999k
            $nFormat = floor($number / 1000);
            $suffix  = 'K+';
        } elseif ($number >= 1000000 && $number < 1000000000) {
            // 1m-999m
            $nFormat = floor($number / 1000000);
            $suffix  = 'M+';
        } elseif ($number >= 1000000000 && $number < 1000000000000) {
            // 1b-999b
            $nFormat = floor($number / 1000000000);
            $suffix  = 'B+';
        } elseif ($number >= 1000000000000) {
            // 1t+
            $nFormat = floor($number / 1000000000000);
            $suffix  = 'T+';
        }

        return empty($nFormat . $suffix) ? 0 : $nFormat . $suffix;
    }
}

if (!function_exists('total_ram_cpu_usage')) {
    /**
     * Get debug data.
     */
    function total_ram_cpu_usage(): array
    {
        //RAM usage
        $free        = shell_exec('free');
        $free        = trim($free);
        $free_arr    = explode("\n", $free);
        $mem         = explode(" ", $free_arr[1]);
        $mem         = array_filter($mem);
        $mem         = array_merge($mem);
        $usedmem     = $mem[2];
        $usedmemInGB = number_format($usedmem / 1048576, 2) . ' GB';
        $memory1     = $mem[2] / $mem[1] * 100;
        $memory      = round($memory1) . '%';
        $fh          = fopen('/proc/meminfo', 'r');
        $mem         = 0;
        while ($line = fgets($fh)) {
            $pieces = array();
            if (preg_match('/^MemTotal:\s+(\d+)\skB$/', $line, $pieces)) {
                $mem = $pieces[1];
                break;
            }
        }
        fclose($fh);
        $totalram = number_format($mem / 1048576, 2) . ' GB';

        //cpu usage
        $cpu_load = sys_getloadavg();
        $load     = $cpu_load[0] . '% / 100%';

        return compact('memory', 'totalram', 'usedmemInGB', 'load');
    }
}

if (!function_exists('convertToNumber')) {
    /**
     * Convert string into corresponding number.
     * If string contains dot or comma, it will be converted to float, otherwise to int.
     * If we receive a digits already we just return them.
     */
    function convertToNumber(string|int|float|null $value): int|float
    {
        if (is_null($value)) {
            return 0;
        }
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        return (str_contains($value, '.') || str_contains($value, ',')) ? (float)$value : (int)$value;
    }
}

if (!function_exists('dateDiffInDays')) {
    /**
     * Find difference between two dates in days
     */
    function dateDiffInDays(string $startDate, string $endDate): int
    {
        $startDate = Carbon\Carbon::parse($startDate);
        $endDate   = Carbon\Carbon::parse($endDate);
        $diff      = $startDate->diff($endDate);
        return $diff->days;
    }
}

if (!function_exists('generate_cache_key')) {
    /**
     * Generate cache key from array.
     */
    function generate_cache_key(array $array): string
    {
        $cacheKey = '';

        foreach ($array as $value) {
            $cacheKey .= is_array($value) ? generate_cache_key($value) : $value . '_';
        }

        return rtrim($cacheKey, '_');
    }
}

if (!function_exists('sanitize_string')) {
    /**
     * Sanitize string removing all unnecessary characters.
     */
    function sanitize_string(?string $input): string
    {
        return trim(str_replace(["\r", "\n", "\t", '\r', '\n', '\t', '  '], ' ', trim((string)$input)), " \"\t\n\r\0\x0B");
    }
}

if (!function_exists('remove_emoji')) {
    function remove_emoji(string $string): string
    {
        $replacement = '';

        /**
         * @see https://unicode.org/charts/PDF/UFE00.pdf
         */
        $variantSelectors = '[\x{FE00}–\x{FE0F}]?'; // ? - optional

        /**
         * There are many sets of modifiers
         * such as skin color modifiers and etc
         *
         * Not used, because this range already included
         * in 'Match Miscellaneous Symbols and Pictographs' range
         * $skin_modifiers = '[\x{1F3FB}-\x{1F3FF}]';
         *
         * Full list of modifiers:
         * @see https://unicode.org/emoji/charts/full-emoji-modifiers.html
         */

        // Match Enclosed Alphanumeric Supplement
        $regexAlphanumeric = "/[\x{1F100}-\x{1F1FF}]$variantSelectors/u";
        $clearString       = preg_replace($regexAlphanumeric, $replacement, $string);

        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = "/[\x{1F300}-\x{1F5FF}]$variantSelectors/u";
        $clearString  = preg_replace($regexSymbols, $replacement, $clearString);

        // Match Emoticons
        $regexEmoticons = "/[\x{1F600}-\x{1F64F}]$variantSelectors/u";
        $clearString    = preg_replace($regexEmoticons, $replacement, $clearString);

        // Match Transport And Map Symbols
        $regexTransport = "/[\x{1F680}-\x{1F6FF}]$variantSelectors/u";
        $clearString    = preg_replace($regexTransport, $replacement, $clearString);

        // Match Supplemental Symbols and Pictographs
        $regexSupplemental = "/[\x{1F900}-\x{1F9FF}]$variantSelectors/u";
        $clearString       = preg_replace($regexSupplemental, $replacement, $clearString);

        // Match Miscellaneous Symbols
        $regexMisc   = "/[\x{2600}-\x{26FF}]$variantSelectors/u";
        $clearString = preg_replace($regexMisc, $replacement, $clearString);

        // Match Dingbats
        $regexDingbats = "/[\x{2700}-\x{27BF}]$variantSelectors/u";
        $clearString   = preg_replace($regexDingbats, $replacement, $clearString);

        return $clearString;
    }
}

if (!function_exists('generate_connection_array')) {
    /**
     * Generate connections array
     */
    function generate_connection_array(string $field_name, array $array): array
    {
        $connection_array = [];
        foreach ($array as $param) {
            $connection_array[] = [$field_name => $param];
        }

        return $connection_array;
    }
}

if (!function_exists('custom_implode')) {
    /**
     * Customized implode helper
     */
    function custom_implode(array $array, string $separator = ', ', string $prepend = '', string $append = ''): string
    {
        return $prepend . implode($separator, $array) . $append;
    }
}
