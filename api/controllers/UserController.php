<?php

class UserController
{
    public static function getAllUsers(): void
    {
        $database = new Database();
        $conn = $database->getConnection();

        $users = (new User($conn))->getAll();
        Response::json($users);
    }

    public static function getUserById(string $id): void
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if ($id === false || $id < 1) {
            Response::error('Invalid id', 400);
            return;
        }

        $database = new Database();
        $conn = $database->getConnection();

        $user = (new User($conn))->getById($id);

        if (!$user) {
            Response::error('User not found', 404);
            return;
        }

        Response::json($user);
    }
}
