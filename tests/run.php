<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/Core/Autoloader.php';\App\Core\Autoloader::register(dirname(__DIR__));
function ok(bool $v,string $m):void{if(!$v)throw new RuntimeException("Falhou: $m");echo "OK: $m\n";}
$v=new \App\Services\CpfCnpjValidator();ok($v->valid('529.982.247-25','PF'),'CPF válido');ok(!$v->valid('111.111.111-11','PF'),'CPF inválido');ok($v->valid('04.252.011/0001-10','PJ'),'CNPJ válido');
$i=(new \App\Services\ContactImporter())->extract('ana@exemplo.com, ana@exemplo.com e bob@example.org');ok(count($i['emails'])===2,'deduplicação de e-mails');
$b=(new \App\Services\BatchPlanner())->plan(12,200,new DateTimeImmutable('2026-01-01 09:00:00'));ok(count($b)===3&&$b[0]['size']===5&&$b[2]['size']===2,'lotes rígidos de cinco');
echo "Todos os testes unitários passaram.\n";
