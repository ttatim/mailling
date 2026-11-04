<?php
declare(strict_types=1);

use App\Core\{Autoloader,Database};
require dirname(__DIR__).'/app/Core/Autoloader.php';
Autoloader::register(dirname(__DIR__));
$config=require dirname(__DIR__).'/config.php';
$password=getenv('ADMIN_PASSWORD');
if (!is_string($password) || strlen($password)<8) { fwrite(STDERR,"Defina ADMIN_PASSWORD com pelo menos 8 caracteres.\n"); exit(1); }
$db=Database::connect($config);
$stmt=$db->prepare("INSERT INTO users(full_name,email,password_hash,phone,person_type,document_ciphertext,document_hash,email_verified_at,status,role) VALUES(:name,:email,:password,'00000000000','PF','ADMIN_LOCAL_SETUP',SHA2(:email,256),NOW(),'active','super_admin') ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash),role='super_admin',status='active',email_verified_at=NOW()");
$stmt->execute(['name'=>'Thiago TatiM','email'=>'thiago.tatim@gmail.com','password'=>password_hash($password,PASSWORD_BCRYPT)]);
echo "Super-admin criado/atualizado.\n";
