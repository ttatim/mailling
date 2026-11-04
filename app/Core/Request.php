<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function method(): string { return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'); }
    public function path(): string { return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'; }
}
