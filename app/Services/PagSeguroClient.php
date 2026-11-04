<?php
declare(strict_types=1);
namespace App\Services;
/** Minimal HTTP adapter. Payment fields remain hosted/tokenized by PagBank; card PAN never reaches this application. */
final class PagSeguroClient {
 public function __construct(private array $config) {}
 public function createCheckout(array $payload):array {return $this->request('POST','/checkouts',$payload);}
 public function cancelSubscription(string $id):array {return $this->request('DELETE','/subscriptions/'.rawurlencode($id));}
 private function request(string $method,string $path,array $payload=[]):array{$base=($this->config['environment']??'sandbox')==='production'?'https://api.pagseguro.com':'https://sandbox.api.pagseguro.com';$h=curl_init($base.$path);curl_setopt_array($h,[CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$this->config['token'],'Content-Type: application/json','Accept: application/json'],CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20]);if($payload)curl_setopt($h,CURLOPT_POSTFIELDS,json_encode($payload,JSON_THROW_ON_ERROR));$body=curl_exec($h);$code=(int)curl_getinfo($h,CURLINFO_HTTP_CODE);if($body===false||$code<200||$code>=300)throw new \RuntimeException('PagSeguro indisponível: '.curl_error($h));return json_decode($body,true,512,JSON_THROW_ON_ERROR);}
}
