<?php
session_start();

require_once __DIR__ . '/../api/config/database.php';
require_once __DIR__ . '/../api/config/jwt.php';
require_once __DIR__ . '/../api/models/Users.php';
require_once __DIR__ . '/../api/models/Products.php';

$users = [];
$products = [];
$loginError = '';

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: /front/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $loginError = 'Veuillez saisir un email et un mot de passe.';
    } else {
        $database = new Database();
        $conn = $database->getConnection();

        if (!$conn) {
            $loginError = 'Connexion à la base impossible.';
        } else {
            $usersModel = new Users($conn);
            $user = $usersModel->findAuthByEmail($email);

            $isValid = $user && (password_verify($password, $user['password']) || hash_equals($user['password'], $password));

            if (!$isValid) {
                $loginError = 'Identifiants invalides.';
            } else {
                $jwtSecret = getJwtSecret();

                if (!$jwtSecret) {
                    $loginError = 'JWT secret manquant dans .env.';
                } else {
                    $now = time();
                    $token = createJwt([
                        'sub' => $user['id'],
                        'email' => $user['email'],
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'iat' => $now,
                        'exp' => $now + 3600,
                    ], $jwtSecret);

                    $_SESSION['token'] = $token;
                    $_SESSION['user_email'] = $user['email'];

                    header('Location: /front/index.php');
                    exit;
                }
            }
        }
    }
}

$isConnected = !empty($_SESSION['token']);
$frontToken = $isConnected ? ($_SESSION['token'] ?? '') : '';
$frontUserEmail = $isConnected ? ($_SESSION['user_email'] ?? '') : '';

if ($isConnected && isset($_GET['action']) && $_GET['action'] === 'all') {
    $database = new Database();
    $conn = $database->getConnection();

    if ($conn) {
        $users = (new Users($conn))->getAll();
        $products = (new Products($conn))->getAll();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP API</title>
    <link rel="icon" href="/public/favicon.png" type="image/png">
</head>

<body>
    <header style="display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 20px;">
        <h1 style="margin: 0;"><a style="text-decoration: none" href='http://localhost:8080/front/index.php'>Hello PHP Learn</a></h1>
        <?php if (!$isConnected): ?>
            <p style="margin: 0; color: #b00020;">Veuillez vous connecter.</p>
            <?php if ($loginError !== ''): ?>
                <p style="margin: 0; color: #b00020;"><?= htmlspecialchars($loginError) ?></p>
            <?php endif; ?>
            <form action="/front/index.php" method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit">Connexion</button>
            </form>
        <?php else: ?>
            <p style="margin: 0; color: #1b5e20;">Connecté : <?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
            <div style="display: flex; gap: 10px;">
                <a href="?action=all"><button type="button">Get DATA</button></a>
                <a href="?action=clear"><button type="button">Clear</button></a>
                <a href="?action=logout"><button type="button">Déconnexion</button></a>
            </div>
        <?php endif; ?>
    </header>

    <?php if ($isConnected): ?>
        <div style="display: flex; justify-content: center; gap: 40px;">
            <div>
                <table border="1" cellpadding="8" cellspacing="0" style="width: 400px; white-space: nowrap;">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['first_name']) ?></td>
                                <td><?= htmlspecialchars($user['last_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div>
                <table border="1" cellpadding="8" cellspacing="0" style="width: 500px; white-space: nowrap;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td style="background-color: green; opacity: 80%"><?= htmlspecialchars($product['price']) ?></td>
                                <td><?= htmlspecialchars($product['category']) ?></td>
                                <td><?= htmlspecialchars($product['stock']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <script>
        (() => {
            const token = <?= json_encode($frontToken) ?>;
            const userEmail = <?= json_encode($frontUserEmail) ?>;

            if (token) {
                sessionStorage.setItem('token', token);
                if (userEmail) {
                    sessionStorage.setItem('user_email', userEmail);
                }
            } else {
                sessionStorage.removeItem('token');
                sessionStorage.removeItem('user_email');
            }
        })();
    </script>
</body>

</html>