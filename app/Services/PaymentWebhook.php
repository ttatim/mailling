<?php
declare(strict_types=1);
namespace App\Services;
final class PaymentWebhook { public static function verify(string $body,?string $signature,string $secret):bool { return $secret!=='' && is_string($signature) && hash_equals(hash_hmac('sha256',$body,$secret),$signature); } }
