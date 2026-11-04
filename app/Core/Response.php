<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function html(string $content, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=UTF-8');
        echo $content;
        exit;
    }
    public static function redirect(string $path): never { header('Location: ' . $path, true, 302); exit; }
    public static function json(array $data): never { header('Content-Type: application/json'); echo json_encode($data, JSON_THROW_ON_ERROR); exit; }
}
