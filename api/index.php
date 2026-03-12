<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/models/Users.php";
require_once __DIR__ . "/models/Products.php";
require_once __DIR__ . "/routes/userRoutes.php";
require_once __DIR__ . "/routes/productsRoutes.php";

header("Content-Type: application/json");

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];
$endpoint = $_GET["endpoint"] ?? null;

switch ($endpoint) {
    case "users":
        handleUsers($method, $conn);
        break;
    case "products":
        handleProducts($method, $conn);
        break;
    default:
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found", "available" => ["users", "products"]]);
        break;
}
