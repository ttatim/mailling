<?php

declare(strict_types=1);

use App\Core\Autoloader;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Controllers\AppController;
use App\Controllers\HealthController;

require __DIR__ . '/app/Core/Autoloader.php';
Autoloader::register(__DIR__);

$config = require __DIR__ . '/config.php';
Session::start($config['security']['session_name']);
header('X-Content-Type-Options: nosniff'); header('X-Frame-Options: DENY'); header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self'");
$app = new AppController(); $router = new Router();
$health = new HealthController($config);
$router->get('/', [$app,'home']); $router->get('/login',[$app,'loginForm']);$router->post('/login',[$app,'login']);$router->get('/cadastro',[$app,'registerForm']);$router->post('/cadastro',[$app,'register']);$router->post('/sair',[$app,'logout']);
$router->get('/esqueci-senha',[$app,'forgotForm']);$router->post('/esqueci-senha',[$app,'forgot']);$router->get('/redefinir',[$app,'resetForm']);$router->post('/redefinir',[$app,'reset']);
$router->get('/ativar',[$app,'activate']);
$router->get('/health',[$health,'check']);
$router->get('/painel',[$app,'dashboard']);$router->get('/contatos',[$app,'contacts']);$router->post('/contatos/importar',[$app,'import']);$router->post('/contatos/excluir',[$app,'deleteContact']);
$router->get('/planos',[$app,'plans']);$router->post('/checkout',[$app,'checkout']);
$router->get('/notificacoes',[$app,'notifications']);
$router->get('/contatos/exportar',[$app,'exportContacts']);
$router->get('/configuracoes',[$app,'settings']);$router->post('/configuracoes/cancelar',[$app,'cancel']);
$router->post('/configuracoes/smtp',[$app,'senderSave']);
$router->get('/campanhas/nova',[$app,'campaignForm']);$router->post('/campanhas',[$app,'campaignCreate']);$router->post('/campanhas/finalizar',[$app,'campaignFinalize']);$router->post('/campanhas/status',[$app,'campaignStatus']);
$router->get('/descadastro',[$app,'optoutForm']);$router->post('/descadastro',[$app,'optout']);
$router->get('/admin',[$app,'admin']);$router->post('/admin/bloquear',[$app,'adminBlock']);$router->post('/admin/desbloquear',[$app,'adminUnblock']);$router->post('/webhooks/pagamentos',[$app,'webhook']);
$router->dispatch(new Request());
