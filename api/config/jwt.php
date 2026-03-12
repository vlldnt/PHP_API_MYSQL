<?php

function base64UrlEncode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function createJwt(array $payload, string $secret): string
{
    $header = [
        'alg' => 'HS256',
        'typ' => 'JWT',
    ];

    $encodedHeader = base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
    $encodedPayload = base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));
    $signature = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $secret, true);
    $encodedSignature = base64UrlEncode($signature);

    return $encodedHeader . '.' . $encodedPayload . '.' . $encodedSignature;
}

function getJwtSecret(): ?string
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
