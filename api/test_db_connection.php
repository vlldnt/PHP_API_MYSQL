<?php

require_once __DIR__ . '/database.php';

$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    echo ("KO: database connection failed\n");
    exit(404);
}
echo "Bien connecté à la base de données : " . $database->getDbName() . "\n";
exit(200);
