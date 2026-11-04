<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, callable> */ private array $routes = []; private $before = null;

    public function get(string $path, callable $handler): void { $this->routes['GET ' . $path] = $handler; }
    public function post(string $path, callable $handler): void { $this->routes['POST ' . $path] = $handler; }
    public function before(callable $middleware): void { $this->before = $middleware; }
    public function dispatch(Request $request): void
    {
        if ($this->before) ($this->before)($request);
        $handler = $this->routes[$request->method() . ' ' . $request->path()] ?? null;
        if ($handler === null) Response::html('<h1>404</h1>', 404);
        $handler($request);
    }
}
