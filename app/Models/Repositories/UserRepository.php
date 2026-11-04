<?php
declare(strict_types=1);
namespace App\Models\Repositories;
use PDO;
final class UserRepository {
 public function __construct(private PDO $db) {}
 public function find(int $id): ?array {$s=$this->db->prepare('SELECT * FROM users WHERE id=:id LIMIT 1');$s->execute(['id'=>$id]);return $s->fetch()?:null;}
 public function findByEmail(string $email): ?array {$s=$this->db->prepare('SELECT * FROM users WHERE email=:email LIMIT 1');$s->execute(['email'=>$email]);return $s->fetch()?:null;}
 public function create(array $d): int {$s=$this->db->prepare('INSERT INTO users(full_name,email,password_hash,phone,person_type,document_ciphertext,document_hash) VALUES(:name,:email,:password,:phone,:type,:cipher,:hash)');$s->execute($d);return (int)$this->db->lastInsertId();}
 public function activeSubscription(int $id): bool {$s=$this->db->prepare("SELECT 1 FROM subscriptions s JOIN users u ON u.id=s.user_id WHERE s.user_id=:id AND u.status='active' AND u.access_blocked_at IS NULL AND s.status='active' AND (s.ends_at IS NULL OR s.ends_at > NOW()) LIMIT 1");$s->execute(['id'=>$id]);return(bool)$s->fetchColumn();}
 public function verify(int $id):void{$s=$this->db->prepare("UPDATE users SET email_verified_at=NOW(),status='active' WHERE id=:id");$s->execute(['id'=>$id]);}
 public function createToken(int $id,string $purpose):string{$token=bin2hex(random_bytes(32));$s=$this->db->prepare('INSERT INTO auth_tokens(user_id,token_hash,purpose,expires_at) VALUES(:u,:t,:p,DATE_ADD(NOW(),INTERVAL 24 HOUR))');$s->execute(['u'=>$id,'t'=>hash('sha256',$token),'p'=>$purpose]);return $token;}
 public function useToken(string $token,string $purpose):?array{$s=$this->db->prepare('SELECT * FROM auth_tokens WHERE token_hash=:t AND purpose=:p AND used_at IS NULL AND expires_at>NOW() LIMIT 1');$s->execute(['t'=>hash('sha256',$token),'p'=>$purpose]);$r=$s->fetch();if(!$r)return null;$this->db->prepare('UPDATE auth_tokens SET used_at=NOW() WHERE id=:id')->execute(['id'=>$r['id']]);return $r;}
 public function resetPassword(int $id,string $hash):void{$s=$this->db->prepare('UPDATE users SET password_hash=:p WHERE id=:id');$s->execute(['p'=>$hash,'id'=>$id]);}
 public function notifications(int $id):array{$s=$this->db->prepare('SELECT * FROM user_notifications WHERE user_id=:id ORDER BY created_at DESC LIMIT 100');$s->execute(['id'=>$id]);return$s->fetchAll();}
 public function canUseApp(int $id):bool{$u=$this->find($id);return $u&&($u['role']==='super_admin'||$this->activeSubscription($id));}
}
