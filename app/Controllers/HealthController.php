<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\{Database,Response};
final class HealthController {public function __construct(private array $config){}public function check():never{try{Database::connect($this->config)->query('SELECT 1');Response::json(['ok'=>true,'time'=>gmdate('c'),'environment'=>$this->config['app']['environment']]);}catch(\Throwable){http_response_code(503);Response::json(['ok'=>false]);}}}
