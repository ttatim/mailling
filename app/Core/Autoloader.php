<?php

declare(strict_types=1);

namespace App\Core;

final class Autoloader
{
    public static function register(string $basePath): void
    {
        spl_autoload_register(static function (string $class) use ($basePath): void {
            $prefix = 'App\\';
            if (!str_starts_with($class, $prefix)) return;
            $file = $basePath . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) require $file;
        });
    }
}
