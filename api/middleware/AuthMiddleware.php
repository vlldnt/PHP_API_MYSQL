<?php

class AuthMiddleware
{
    public static function authenticate(): void
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!preg_match('/^Bearer\s+(.+)$/', $header, $matches)) {
            Response::error('Token required', 401);
            exit;
        }

        $secret = JwtHandler::getSecret();

        if (!$secret) {
            Response::error('JWT secret is not configured', 500);
            exit;
        }

        $payload = JwtHandler::decode($matches[1], $secret);

        if (!$payload) {
            Response::error('Invalid or expired token', 401);
            exit;
        }

        $GLOBALS['auth_user'] = $payload;
    }
}
