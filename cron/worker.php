<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register(dirname(__DIR__));

$config = require dirname(__DIR__) . '/config.php';
$db = \App\Core\Database::connect($config);
(new \App\Services\BillingCycle(new \App\Models\Repositories\BillingRepository($db),new \App\Services\PagSeguroClient($config['billing']),new \App\Services\PlatformMailer($config),$config['app']))->run();
(new \App\Services\AccountLifecycle($db))->notifyUpcoming();
(new \App\Services\AccountLifecycle($db))->closeDue();
(new \App\Services\AccountLifecycle($db))->closeSingleUse();
// Lock a single due batch; SKIP LOCKED lets concurrent cron invocations coexist safely.
$db->beginTransaction();
$batch = $db->query("SELECT b.* FROM campaign_batches b JOIN campaigns c ON c.id=b.campaign_id WHERE b.status='pending' AND b.scheduled_at<=NOW() AND c.status IN ('queued','running') ORDER BY b.scheduled_at LIMIT 1 FOR UPDATE SKIP LOCKED")->fetch();
if (!$batch) { $db->commit(); exit(0); }
$lock = $db->prepare("UPDATE campaign_batches SET status='processing',locked_at=NOW(),locked_by=:worker WHERE id=:id AND status='pending'");
$lock->execute(['worker'=>gethostname() ?: 'worker','id'=>$batch['id']]);
if (!$lock->rowCount()) { $db->rollBack(); exit(0); }
$db->commit();
// The delivery service claims each recipient by status before SMTP delivery. A failed item can be retried up to 3 times.
$recipients=$db->prepare("SELECT * FROM campaign_recipients WHERE batch_id=:id AND status='pending' AND attempts<3 FOR UPDATE SKIP LOCKED");
$db->beginTransaction();$recipients->execute(['id'=>$batch['id']]);$rows=$recipients->fetchAll();$claim=$db->prepare("UPDATE campaign_recipients SET status='sending',attempts=attempts+1 WHERE id=:id AND status='pending'");foreach($rows as $row)$claim->execute(['id'=>$row['id']]);$db->commit();
$campaignQuery=$db->prepare('SELECT c.*,u.id user_id,s.display_name,s.from_email,s.reply_to_email,s.smtp_host,s.smtp_port,s.smtp_username,s.smtp_password_ciphertext,s.smtp_security FROM campaigns c JOIN sender_settings s ON s.user_id=c.user_id JOIN users u ON u.id=c.user_id WHERE c.id=:id');
$campaignQuery->execute(['id'=>$batch['campaign_id']]);$campaign=$campaignQuery->fetch();
if (!$campaign) { fwrite(STDERR,"Sender settings missing for campaign {$batch['campaign_id']}\n"); exit(1); }
$campaign['password']=(new \App\Services\Crypto($config['app']['encryption_key']))->decrypt($campaign['smtp_password_ciphertext']);
$mailer=new \App\Services\SmtpMailer();$appUrl=rtrim($config['app']['url'],'/');
$sent=$db->prepare("UPDATE campaign_recipients SET status='sent',sent_at=NOW(),last_error=NULL WHERE id=:id AND status='sending'");
$failed=$db->prepare("UPDATE campaign_recipients SET status=IF(attempts>=3,'failed','pending'),last_error=:error WHERE id=:id AND status='sending'");
foreach($rows as $row){try{$tokenQuery=$db->prepare('SELECT unsubscribe_token FROM contacts WHERE id=:id');$tokenQuery->execute(['id'=>$row['contact_id']]);$token=(string)$tokenQuery->fetchColumn();$url=$appUrl.'/descadastro?token='.rawurlencode($token);$footer='<hr><p style="font-size:12px">Não deseja receber estas mensagens? <a href="'.htmlspecialchars($url,ENT_QUOTES,'UTF-8').'">Cancele sua inscrição</a>.</p>';$html=str_replace('{nome}',htmlspecialchars((string)($row['recipient_name']?:''),ENT_QUOTES,'UTF-8'),$campaign['body_html']).$footer;$text=str_replace('{nome}',(string)($row['recipient_name']?:''),$campaign['body_text'])."\n\nDescadastro: $url";$mailer->send($campaign,$row['email'],$campaign['subject'],$html,$text,$campaign['from_email'],$campaign['reply_to_email']??'');$sent->execute(['id'=>$row['id']]);}catch(\Throwable $e){$failed->execute(['id'=>$row['id'],'error'=>substr($e->getMessage(),0,2000)]);}}
$pending=$db->prepare("SELECT COUNT(*) FROM campaign_recipients WHERE batch_id=:id AND status IN ('pending','sending')");$pending->execute(['id'=>$batch['id']]);$batchStatus=$pending->fetchColumn()?'pending':'completed';$db->prepare('UPDATE campaign_batches SET status=:status,completed_at=IF(:status="completed",NOW(),NULL) WHERE id=:id')->execute(['status'=>$batchStatus,'id'=>$batch['id']]);
$db->prepare("UPDATE campaigns SET status='running' WHERE id=:id AND status='queued'")->execute(['id'=>$batch['campaign_id']]);
$left=$db->prepare("SELECT COUNT(*) FROM campaign_batches WHERE campaign_id=:id AND status IN ('pending','processing')");$left->execute(['id'=>$batch['campaign_id']]);if(!(int)$left->fetchColumn())$db->prepare("UPDATE campaigns SET status='completed' WHERE id=:id")->execute(['id'=>$batch['campaign_id']]);
