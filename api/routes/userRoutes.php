<?php

function handleUsers(string $method, PDO $conn): void
{
    $usersModel = new Users($conn);

    switch ($method) {
        case "GET":
            if (isset($_GET['id'])) {
                $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

                if ($id === false || $id < 1) {
                    http_response_code(400);
                    echo json_encode(["error" => "Invalid id"]);
                    return;
                }

                $user = $usersModel->getById($id);

                if (!$user) {
                    http_response_code(404);
                    echo json_encode(["error" => "User not found"]);
                    return;
                }

                echo json_encode($user);
                return;
            }

            echo json_encode($usersModel->getAll());
            break;
        default:
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            break;
    }
}
