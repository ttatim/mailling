<?php
declare(strict_types=1);
namespace App\Models\Repositories;
use PDO;
final class BillingRepository {public function __construct(private PDO $db){}
 public function plans():array{return$this->db->query('SELECT * FROM plans WHERE active=1 ORDER BY amount_cents')->fetchAll();}
 public function plan(string $code):?array{$s=$this->db->prepare('SELECT * FROM plans WHERE code=:c AND active=1');$s->execute(['c'=>$code]);return$s->fetch()?:null;}
 public function createPending(int $user,array $plan,string $method,string $reference):int{$this->db->beginTransaction();try{$s=$this->db->prepare("INSERT INTO subscriptions(user_id,plan_id,provider,provider_reference,payment_method,status) VALUES(:u,:p,'pagseguro',:r,:m,'pending')");$s->execute(['u'=>$user,'p'=>$plan['id'],'r'=>$reference,'m'=>$method]);$sub=(int)$this->db->lastInsertId();$f=$this->db->prepare('INSERT INTO financial_transactions(user_id,subscription_id,provider,provider_reference,amount_cents,status,due_at) VALUES(:u,:s,\'pagseguro\',:r,:a,\'pending\',DATE_ADD(NOW(),INTERVAL 10 DAY))');$f->execute(['u'=>$user,'s'=>$sub,'r'=>$reference,'a'=>$plan['amount_cents']]);$this->db->commit();return$sub;}catch(\Throwable $e){$this->db->rollBack();throw$e;}}
 public function checkoutUrl(string $reference,string $url):void{$s=$this->db->prepare('UPDATE financial_transactions SET payment_url=:url WHERE provider=\'pagseguro\' AND provider_reference=:r');$s->execute(['url'=>$url,'r'=>$reference]);}
 public function cancellableSubscriptions():array{return$this->db->query("SELECT id,provider_reference FROM subscriptions WHERE provider='pagseguro' AND status='cancelled' AND provider_reference IS NOT NULL")->fetchAll();}
 public function markProviderCancelled(int $id):void{$s=$this->db->prepare("UPDATE subscriptions SET provider_reference=NULL WHERE id=:id");$s->execute(['id'=>$id]);}
 public function dueInvoices():array{return$this->db->query("SELECT s.*,p.name,p.amount_cents,u.email FROM subscriptions s JOIN plans p ON p.id=s.plan_id JOIN users u ON u.id=s.user_id WHERE s.status='active' AND s.payment_method IN ('pix','boleto') AND (s.ends_at IS NULL OR s.ends_at<=DATE_ADD(NOW(),INTERVAL 10 DAY))")->fetchAll();}
 public function invoice(int $sub,string $reference,string $url,int $amount):void{$s=$this->db->prepare("INSERT INTO financial_transactions(user_id,subscription_id,provider,provider_reference,payment_url,amount_cents,status,due_at) SELECT user_id,:s,'pagseguro',:r,:url,:a,'pending',DATE_ADD(NOW(),INTERVAL 10 DAY) FROM subscriptions WHERE id=:s");$s->execute(['s'=>$sub,'r'=>$reference,'url'=>$url,'a'=>$amount]);}
}
