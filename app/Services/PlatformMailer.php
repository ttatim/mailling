<?php
declare(strict_types=1);
namespace App\Services;
final class PlatformMailer {public function __construct(private array $config){} public function send(string $to,string $subject,string $html):void{$s=$this->config['platform_smtp']??null;if(!$s)throw new \RuntimeException('SMTP da plataforma não configurado.');$s['password']=(new Crypto($this->config['app']['encryption_key']))->decrypt($s['password_ciphertext']);(new SmtpMailer())->send($s,$to,$subject,$html,strip_tags($html),$s['from_email'],$s['reply_to_email']??'');}}
