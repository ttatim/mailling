<?php
declare(strict_types=1);
namespace App\Models\Repositories;
use PDO;
final class UserRepository {
 public function __construct(private PDO $db) {}
 public function find(int $id): ?array {$s=$this->db->prepare('SELECT * FROM users WHERE id=:id LIMIT 1');$s->execute(['id'=>$id]);return $s->fetch()?:null;}
 public function findByEmail(string $email): ?array {$s=$this->db->prepare('SELECT * FROM users WHERE email=:email LIMIT 1');$s->execute(['email'=>$email]);return $s->fetch()?:null;}
 public function create(array $d): int {$s=$this->db->prepare('INSERT INTO users(full_name,email,password_hash,phone,person_type,document_ciphertext,document_hash) VALUES(:name,:email,:password,:phone,:type,:cipher,:hash)');$s->execute($d);return (int)$this->db->lastInsertId();}
 public function activeSubscription(int $id): bool {$s=$this->db->prepare("SELECT 1 FROM subscriptions s JOIN users u ON u.id=s.user_id WHERE s.user_id=:id AND u.access_blocked_at IS NULL AND s.status='active' AND (s.ends_at IS NULL OR s.ends_at > NOW()) LIMIT 1");$s->execute(['id'=>$id]);return(bool)$s->fetchColumn();}
 public function verify(int $id):void{$s=$this->db->prepare("UPDATE users SET email_verified_at=NOW(),status='active' WHERE id=:id");$s->execute(['id'=>$id]);}
}
