<?php
declare(strict_types=1);
namespace App\Services;
final class Crypto {
 public function __construct(private string $key) {}
 public function encrypt(string $plain): string { $iv=random_bytes(12);$tag='';$cipher=openssl_encrypt($plain,'aes-256-gcm',hash('sha256',$this->key,true),OPENSSL_RAW_DATA,$iv,$tag);if($cipher===false)throw new \RuntimeException('Falha ao cifrar.');return base64_encode($iv.$tag.$cipher); }
 public function decrypt(string $encoded): string { $raw=base64_decode($encoded,true);if($raw===false||strlen($raw)<29)throw new \RuntimeException('Cifra inválida.');$iv=substr($raw,0,12);$tag=substr($raw,12,16);$r=openssl_decrypt(substr($raw,28),'aes-256-gcm',hash('sha256',$this->key,true),OPENSSL_RAW_DATA,$iv,$tag);if($r===false)throw new \RuntimeException('Falha ao decifrar.');return $r; }
}
