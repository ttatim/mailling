<?php

declare(strict_types=1);

use App\Core\Autoloader;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

require __DIR__ . '/app/Core/Autoloader.php';
Autoloader::register(__DIR__);

$router = new Router();
$router->get('/', static function (): void {
    Response::html('<!doctype html><html lang="pt-BR"><body><h1>Mailing</h1><p>Fundação técnica em preparação.</p></body></html>');
});
$router->dispatch(new Request());
