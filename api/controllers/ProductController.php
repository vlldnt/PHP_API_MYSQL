<?php

class ProductController
{
    public static function getAllProducts(): void
    {
        $database = new Database();
        $conn = $database->getConnection();

        $products = (new Product($conn))->getAll();
        Response::json($products);
    }
}
