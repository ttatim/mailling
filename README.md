# Mailing

Micro SaaS de e-mail marketing em PHP 8.3 puro, sem framework, Composer ou dependências externas.

## Implementação

- Estrutura PSR-4 simplificada e autoloader próprio.
- Front controller e regras Apache para rotas amigáveis.
- Schema MySQL 8 completo em `migrations/001_initial_schema.sql`.
- Base segura para credenciais, filas, opt-out global, auditoria, billing e rate limiting, com landing, cadastro/login, contatos e campanhas.

## Decisões de schema para validação

- Opt-out usa **supressão global**: preserva hash do e-mail e motivo, impedindo reimportação/envio para o endereço, mesmo entre contas; o contato é marcado como `unsubscribed` para manter integridade histórica de campanhas.
- Documento é mantido cifrado e também com SHA-256 para unicidade sem expor o valor nas consultas.
- Lotes e destinatários são tabelas separadas para o worker usar transações e `SELECT ... FOR UPDATE`, garantindo idempotência.
- O limite rígido de 5 e-mails a cada 10 minutos será aplicado pelo serviço de fila, além do limite horário configurado (1–200).

## Instalação inicial

1. Copie `config.example.php` para `config.php` e preencha credenciais.
2. Crie o banco com charset `utf8mb4`.
3. Execute `mysql -u USUARIO -p BANCO < migrations/001_initial_schema.sql`.
4. Execute `mysql -u USUARIO -p BANCO < migrations/002_admin_billing.sql` e crie o super-admin localmente: `ADMIN_PASSWORD='sua-senha' php scripts/create-super-admin.php`.
5. Para desenvolvimento: `php -S localhost:8000` na raiz.

Requisitos: PHP 8.3, MySQL 8.0+, extensões `pdo_mysql`, `openssl`, `zip` e `curl`.

Execute `php tests/run.php` para os testes sem PHPUnit. Para o worker: `* * * * * php /caminho/cron/worker.php`.

O super-admin inicial é criado pelo seed e acessa `/admin`. A URL de callback é `POST /webhooks/pagamentos`; ela requer o cabeçalho `X-Webhook-Signature` com HMAC-SHA256 do corpo usando `billing.webhook_secret`. Eventos aprovados desbloqueiam automaticamente o usuário relacionado à referência de pagamento.

O visual é inspirado na organização de painel do Sufee, porém foi implementado com HTML/CSS próprios, evitando dependências externas. Importadores binários DOC/XLS e PDFs complexos têm limitação deliberada: extraem texto bruto/fluxos simples; DOCX/XLSX usam XML do ZIP.
