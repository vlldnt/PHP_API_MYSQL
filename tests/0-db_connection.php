<?php

require_once __DIR__ . '/../api/config/database.php';

$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    echo ("KO: database connection failed\n");
    exit(1);
}
echo "Bien connecté à la base de données : " . $database->getDbName() . "\n";

// Test requête sur les tables users et products
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM users");
    $result = $stmt->fetch();
    echo "Table 'users' OK : " . $result['total'] . " enregistrement(s)\n";

    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM products");
    $result = $stmt->fetch();
    echo "Table 'products' OK : " . $result['total'] . " enregistrement(s)\n";

    // Non existent table test
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM nonexistant");
    $result = $stmt->fetch();
    echo "Table 'products' OK : " . $result['total'] . " enregistrement(s)\n";

} catch (PDOException $e) {
    echo "Erreur requête SQL : " . $e->getMessage() . "\n";
}

// Déconnexion
$pdo = null;
$database->close();
echo "Déconnexion effectuée.\n";
