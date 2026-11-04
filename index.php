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
set_exception_handler(static function (Throwable $e) use ($config): void { $id='indisponível';try{$id=(new \App\Services\ErrorReporter(\App\Core\Database::connect($config),$config))->report(500,$e);}catch(\Throwable){error_log($e->getMessage());}http_response_code(500);header('Content-Type: text/html; charset=UTF-8');echo '<!doctype html><title>Erro temporário</title><main><h1>Não foi possível concluir esta ação</h1><p>Nossa equipe foi notificada. Informe o código <strong>'.htmlspecialchars($id,ENT_QUOTES,'UTF-8').'</strong> ao suporte.</p></main>'; });
Session::start($config['security']['session_name']);
header('X-Content-Type-Options: nosniff'); header('X-Frame-Options: DENY'); header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self'");
$app = new AppController(); $router = new Router();
$router->before([$app,'enforceAccess']);
$router->onError(static function(int $status,string $message,$request) use($config): void { try{(new \App\Services\ErrorReporter(\App\Core\Database::connect($config),$config))->report($status,new \RuntimeException($message));}catch(\Throwable){} http_response_code($status);header('Content-Type: text/html; charset=UTF-8');echo '<!doctype html><title>Página indisponível</title><main><h1>Não encontramos esta página</h1><p>O ocorrido foi registrado. Volte ao painel ou tente novamente.</p></main>';exit;});
$health = new HealthController($config);
$router->get('/', [$app,'home']); $router->get('/login',[$app,'loginForm']);$router->post('/login',[$app,'login']);$router->get('/cadastro',[$app,'registerForm']);$router->post('/cadastro',[$app,'register']);$router->post('/sair',[$app,'logout']);
$router->get('/termos',[$app,'terms']);$router->get('/privacidade',[$app,'privacy']);
$router->get('/esqueci-senha',[$app,'forgotForm']);$router->post('/esqueci-senha',[$app,'forgot']);$router->get('/redefinir',[$app,'resetForm']);$router->post('/redefinir',[$app,'reset']);
$router->get('/ativar',[$app,'activate']);
$router->get('/health',[$health,'check']);
$router->get('/painel',[$app,'dashboard']);$router->get('/contatos',[$app,'contacts']);$router->post('/contatos/importar',[$app,'import']);$router->post('/contatos/excluir',[$app,'deleteContact']);
$router->post('/contatos/excluir-em-massa',[$app,'deleteContacts']);$router->get('/campanhas/status',[$app,'campaignDashboard']);
$router->get('/planos',[$app,'plans']);$router->post('/checkout',[$app,'checkout']);
$router->get('/notificacoes',[$app,'notifications']);
$router->get('/contatos/exportar',[$app,'exportContacts']);
$router->get('/configuracoes',[$app,'settings']);$router->post('/configuracoes/cancelar',[$app,'cancel']);
$router->post('/configuracoes/smtp',[$app,'senderSave']);
$router->post('/configuracoes/smtp-teste',[$app,'smtpTest']);
$router->get('/campanhas/nova',[$app,'campaignForm']);$router->post('/campanhas',[$app,'campaignCreate']);$router->post('/campanhas/finalizar',[$app,'campaignFinalize']);$router->post('/campanhas/status',[$app,'campaignStatus']);
$router->get('/descadastro',[$app,'optoutForm']);$router->post('/descadastro',[$app,'optout']);
$router->get('/admin',[$app,'admin']);$router->post('/admin/bloquear',[$app,'adminBlock']);$router->post('/admin/desbloquear',[$app,'adminUnblock']);$router->post('/webhooks/pagamentos',[$app,'webhook']);
$router->post('/admin/status',[$app,'adminStatus']);$router->post('/admin/liberar-acesso',[$app,'adminGrant']);
$router->post('/admin/usuarios',[$app,'adminCreate']);
$router->get('/admin/logs',[$app,'adminLogs']);
$router->dispatch(new Request());
