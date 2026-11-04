<?php
declare(strict_types=1);
namespace App\Services;
final class BatchPlanner {
 public const MAX_EMAILS_PER_BATCH=5; public const BATCH_INTERVAL_MINUTES=10;
 /** @return array<array{scheduled_at:string,size:int}> */
 public function plan(int $recipients,int $hourlyLimit, ?\DateTimeImmutable $start=null): array { $limit=max(1,min(200,$hourlyLimit));$start=($start??new \DateTimeImmutable())->setTime((int)date('H'),(int)date('i'),0);$out=[];$sentHour=0;$when=$start;while($recipients>0){if($sentHour>=min($limit,30)){$when=$when->modify('+1 hour')->setTime((int)$when->format('H'),0);$sentHour=0;}$size=min(self::MAX_EMAILS_PER_BATCH,$recipients,min($limit,30)-$sentHour);$out[]=['scheduled_at'=>$when->format('Y-m-d H:i:s'),'size'=>$size];$recipients-=$size;$sentHour+=$size;$when=$when->modify('+'.self::BATCH_INTERVAL_MINUTES.' minutes');if((int)$when->format('i')===0)$sentHour=0;}return $out; }
}
