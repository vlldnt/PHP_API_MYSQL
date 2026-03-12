<?php

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

$resource = $uri[1] ?? null;
$id = $uri[2] ?? null;

switch ($resource) {

    // AUTH
    case "login":
        if ($method === "POST") {
            AuthController::login();
        } else {
            Response::error("Method not allowed", 405);
        }
        break;

    // USERS
    case "users":
        AuthMiddleware::authenticate();

        switch ($method) {
            case "GET":
                if ($id) {
                    UserController::getUserById($id);
                } else {
                    UserController::getAllUsers();
                }
                break;
            default:
                Response::error("Method not allowed", 405);
                break;
        }
        break;

    // PRODUCTS
    case "products":
        AuthMiddleware::authenticate();

        switch ($method) {
            case "GET":
                ProductController::getAllProducts();
                break;
            default:
                Response::error("Method not allowed", 405);
                break;
        }
        break;

    default:
        Response::error("Route not found", 404);
        break;
}
