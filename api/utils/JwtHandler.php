<?php

class JwtHandler
{
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function getSecret(): ?string
    {
        $env = parse_ini_file(__DIR__ . '/../../.env');

        if (!$env) {
            return null;
        }

        $secret = $env['JWT_SECRET'] ?? null;

        if (!$secret || trim($secret) === '') {
            return null;
        }

        return $secret;
    }

    public static function encode(array $payload, string $secret): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $encodedHeader = self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $encodedPayload = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $signature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", $secret, true);

        return "$encodedHeader.$encodedPayload." . self::base64UrlEncode($signature);
    }

    public static function decode(string $token, string $secret): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        $expectedSignature = self::base64UrlEncode(
            hash_hmac('sha256', "$encodedHeader.$encodedPayload", $secret, true)
        );

        if (!hash_equals($expectedSignature, $encodedSignature)) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($encodedPayload), true);

        if (!$payload) {
            return null;
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }
}
