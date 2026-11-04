<?php
declare(strict_types=1);
namespace App\Core;

final class View
{
    public static function render(string $template, array $data = []): never {
        extract($data, EXTR_SKIP); $view = dirname(__DIR__) . '/Views/' . $template . '.php';
        ob_start(); require $view; $content = (string) ob_get_clean(); Response::html($content);
    }
    public static function e(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}
