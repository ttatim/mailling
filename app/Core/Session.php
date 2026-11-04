<?php
declare(strict_types=1);
namespace App\Core;

final class Session
{
    public static function start(string $name): void {
        session_name($name); session_set_cookie_params(['httponly'=>true,'secure'=>(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),'samesite'=>'Lax','path'=>'/']);
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    }
    public static function userId(): ?int { return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null; }
    public static function login(int $userId): void { session_regenerate_id(true); $_SESSION['user_id'] = $userId; }
    public static function logout(): void { $_SESSION=[]; session_destroy(); }
    public static function flash(string $key, ?string $message = null): ?string { if ($message !== null) { $_SESSION['_flash'][$key]=$message; return null; } $v=$_SESSION['_flash'][$key]??null; unset($_SESSION['_flash'][$key]); return $v; }
    public static function csrf(): string { return $_SESSION['_csrf'] ??= bin2hex(random_bytes(32)); }
    public static function verifyCsrf(?string $token): bool { return is_string($token) && hash_equals($_SESSION['_csrf'] ?? '', $token); }
}
