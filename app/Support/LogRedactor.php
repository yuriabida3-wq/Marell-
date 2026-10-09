<?php

namespace App\Support;

class LogRedactor
{
    /**
     * Keys (case-insensitive, contains-match) whose values must be masked.
     */
    private const SENSITIVE_KEY_PARTS = [
        'phone',
        'msisdn',
        'receipt',
        'transaction_code',
        'transactioncode',
        'checkoutrequestid',
        'checkout_request_id',
        'password',
        'pin',
        'token',
        'secret',
        'apikey',
        'api_key',
    ];

    private const MASK = '[redacted]';

    /**
     * Recursively redact sensitive values from a context array for logging.
     */
    public static function redact(array $context): array
    {
        $out = [];
        foreach ($context as $key => $value) {
            if (is_string($key) && self::keyIsSensitive($key)) {
                $out[$key] = self::MASK;
                continue;
            }
            if (is_array($value)) {
                $out[$key] = self::redact($value);
                continue;
            }
            if (is_string($value)) {
                $out[$key] = self::maskPhonesInString($value);
                continue;
            }
            $out[$key] = $value;
        }
        return $out;
    }

    /**
     * Redact phone numbers embedded in free text.
     * Keeps the last 4 digits, masks the rest.
     */
    public static function maskPhonesInString(string $s): string
    {
        return preg_replace_callback(
            '/\b(254[17]\d{8})\b/',
            fn ($m) => substr($m[1], 0, 4) . '****' . substr($m[1], -3),
            $s
        ) ?? $s;
    }

    private static function keyIsSensitive(string $key): bool
    {
        $k = strtolower(str_replace([' ', '-'], '_', $key));
        foreach (self::SENSITIVE_KEY_PARTS as $part) {
            if (str_contains($k, $part)) {
                return true;
            }
        }
        return false;
    }
}
