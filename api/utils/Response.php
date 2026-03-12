<?php

class Response
{
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        echo json_encode($data);
    }

    public static function error(string $message, int $status = 400): void
    {
        http_response_code($status);
        echo json_encode(["error" => $message]);
    }
}
