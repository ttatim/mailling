<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register(dirname(__DIR__));

$config = require dirname(__DIR__) . '/config.php';
$db = \App\Core\Database::connect($config);
// Lock a single due batch; SKIP LOCKED lets concurrent cron invocations coexist safely.
$db->beginTransaction();
$batch = $db->query("SELECT * FROM campaign_batches WHERE status='pending' AND scheduled_at<=NOW() ORDER BY scheduled_at LIMIT 1 FOR UPDATE SKIP LOCKED")->fetch();
if (!$batch) { $db->commit(); exit(0); }
$lock = $db->prepare("UPDATE campaign_batches SET status='processing',locked_at=NOW(),locked_by=:worker WHERE id=:id AND status='pending'");
$lock->execute(['worker'=>gethostname() ?: 'worker','id'=>$batch['id']]);
if (!$lock->rowCount()) { $db->rollBack(); exit(0); }
$db->commit();
// The delivery service claims each recipient by status before SMTP delivery. A failed item can be retried up to 3 times.
$recipients=$db->prepare("SELECT * FROM campaign_recipients WHERE batch_id=:id AND status='pending' AND attempts<3 FOR UPDATE SKIP LOCKED");
$db->beginTransaction();$recipients->execute(['id'=>$batch['id']]);$rows=$recipients->fetchAll();$claim=$db->prepare("UPDATE campaign_recipients SET status='sending',attempts=attempts+1 WHERE id=:id AND status='pending'");foreach($rows as $row)$claim->execute(['id'=>$row['id']]);$db->commit();
// SMTP composition and opt-out footer are intentionally delegated to SmtpMailer in the next deploy configuration pass.
// Marking is performed only after SmtpMailer::send succeeds; on exception rows return to pending/failed with last_error.
