<?php

$users = [];
$products = [];

if (isset($_GET['action']) && $_GET['action'] === 'all') {
    require_once __DIR__ . '/../api/config/database.php';
    require_once __DIR__ . '/../api/models/Users.php';
    require_once __DIR__ . '/../api/models/Products.php';

    $database = new Database();
    $conn = $database->getConnection();

    $users = (new Users($conn))->getAll();
    $products = (new Products($conn))->getAll();
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
        <div style="display: flex; gap: 20px;">
            <div>
                <label for="email">Email</label>
                <input type="text" id="email">
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" id="password">
            </div>
            <button>Se connecter</button>
        </div>
    </header>
    <a href="?action=all"><button>Get DATA</button></a>

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
    <a href="?action=clear"><button>Clear</button></a>
</body>

</html>