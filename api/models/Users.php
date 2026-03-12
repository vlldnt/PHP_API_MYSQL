<?php

class Users
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAll()
    {
        $stmt = $this->conn->query("SELECT first_name, last_name, email FROM users");
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function findAuthByEmail(string $email)
    {
        $stmt = $this->conn->prepare("SELECT id, first_name, last_name, email, password FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
}
