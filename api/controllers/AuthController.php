<?php

class AuthController
{
    public static function login(): void
    {
        $rawBody = file_get_contents('php://input');
        $jsonBody = json_decode($rawBody, true);

        $email = trim($jsonBody['email'] ?? '');
        $password = $jsonBody['password'] ?? '';

        if ($email === '' || $password === '') {
            Response::error('Email and password are required', 400);
            return;
        }

        $database = new Database();
        $conn = $database->getConnection();
        $user = (new User($conn))->findByEmail($email);

        $isValid = $user && (password_verify($password, $user['password']) || hash_equals($user['password'], $password));

        if (!$isValid) {
            Response::error('Invalid credentials', 401);
            return;
        }

        $secret = JwtHandler::getSecret();

        if (!$secret) {
            Response::error('JWT secret is not configured', 500);
            return;
        }

        $now = time();

        $token = JwtHandler::encode([
            'sub' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'iat' => $now,
            'exp' => $now + 3600,
        ], $secret);

        Response::json([
            'message' => 'Login success',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]);
    }
}
