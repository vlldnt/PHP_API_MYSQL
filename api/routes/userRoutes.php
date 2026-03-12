<?php

function handleUsers(string $method, PDO $conn): void
{
    $usersModel = new Users($conn);

    switch ($method) {
        case "GET":
            echo json_encode($usersModel->getAll());
            break;
        default:
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            break;
    }
}
