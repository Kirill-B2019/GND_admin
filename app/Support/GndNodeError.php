<?php

namespace App\Support;

/**
 * Разбор ответов/ошибок ноды GND для человекочитаемого UI.
 */
final class GndNodeError
{
    /**
     * @return array{user_message: string, hint: string|null, code: int|string|null, raw: string, is_json: bool}
     */
    public static function parse(?string $body): array
    {
        $raw = trim((string) $body);
        if ($raw === '') {
            return [
                'user_message' => 'Неизвестная ошибка ноды.',
                'hint' => null,
                'code' => null,
                'raw' => '',
                'is_json' => false,
            ];
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $msg = $decoded['error'] ?? $decoded['message'] ?? null;
            if (! is_string($msg) || $msg === '') {
                $msg = $raw;
            }
            $code = $decoded['code'] ?? null;

            return [
                'user_message' => $msg,
                'hint' => self::hintForMessage($msg, $code),
                'code' => $code,
                'raw' => $raw,
                'is_json' => true,
            ];
        }

        return [
            'user_message' => $raw,
            'hint' => self::hintForMessage($raw, null),
            'code' => null,
            'raw' => $raw,
            'is_json' => false,
        ];
    }

    private static function hintForMessage(string $message, mixed $code): ?string
    {
        $lower = mb_strtolower($message);
        if (str_contains($lower, 'x-admin-token') || str_contains($lower, 'admin-token') || $code === 401) {
            return 'Проверьте, что в .env админки и на ноде задано одинаковое значение GND_ADMIN_SECRET, затем перезапустите ноду и выполните php artisan config:clear.';
        }

        return null;
    }
}
