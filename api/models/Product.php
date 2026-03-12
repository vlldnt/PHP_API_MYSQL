<?php

class Product
{
    private $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function getAll(): array
    {
        $stmt = $this->conn->query("SELECT name, price, category, stock FROM products");
        return $stmt->fetchAll();
    }
}
