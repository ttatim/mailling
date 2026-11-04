# Homologação e produção

1. Configure HTTPS, PHP 8.3, MySQL 8 e as extensões `pdo_mysql`, `openssl`, `curl`, `zip`, `fileinfo`.
2. Copie `config.example.php` para `config.php`, com permissões `0600`; configure banco, chave de cifra, SMTP transacional e PagSeguro.
3. Rode `php scripts/migrate.php` e `ADMIN_PASSWORD='...' php scripts/create-super-admin.php`.
4. Faça o servidor apontar o document root à raiz e habilite rewrite Apache. Garanta que `storage/` não seja servido publicamente.
5. Configure cron: `* * * * * /usr/bin/php /caminho/cron/worker.php >> /caminho/storage/logs/worker.log 2>&1`.
6. No PagBank, configure o callback público HTTPS `https://SEU-DOMINIO/webhooks/pagamentos`; use o mesmo segredo HMAC configurado no ambiente.
7. Verifique `GET /health` e execute `php tests/run.php` antes da liberação.

## Checklist de homologação

- Cadastro, confirmação de e-mail, login e recuperação de senha.
- SMTP de plataforma e SMTP de um remetente de teste.
- Importação de CSV/TXT e opt-out.
- Checkout sandbox e callback de pagamento aprovado.
- Cron, envio de campanha e retry de erro SMTP.
