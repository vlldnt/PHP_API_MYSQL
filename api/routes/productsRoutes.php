<?php

function handleProducts(string $method, PDO $conn): void
{
    $productsModel = new Products($conn);

    switch ($method) {
        case "GET":
            echo json_encode($productsModel->getAll());
            break;
        default:
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            break;
    }
}
