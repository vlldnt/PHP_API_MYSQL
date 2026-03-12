<?php

function handleLogin(string $method, PDO $conn): void
{
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $rawBody = file_get_contents('php://input');
    $jsonBody = json_decode($rawBody, true);

    $email = trim($jsonBody['email'] ?? $_POST['email'] ?? '');
    $password = $jsonBody['password'] ?? $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password are required']);
        return;
    }

    $usersModel = new Users($conn);
    $user = $usersModel->findAuthByEmail($email);

    $isValid = $user && (password_verify($password, $user['password']) || hash_equals($user['password'], $password));

    if (!$isValid) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        return;
    }

    $jwtSecret = getJwtSecret();

    if (!$jwtSecret) {
        http_response_code(500);
        echo json_encode(['error' => 'JWT secret is not configured']);
        return;
    }

    $now = time();
    $exp = $now + 3600;

    $token = createJwt([
        'sub' => $user['id'],
        'email' => $user['email'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'iat' => $now,
        'exp' => $exp,
    ], $jwtSecret);

    echo json_encode([
        'message' => 'Login success',
        'token' => $token,
        'token_type' => 'Bearer',
        'expires_in' => 3600,
    ]);
}
