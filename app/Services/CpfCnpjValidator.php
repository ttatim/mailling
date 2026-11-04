<?php
declare(strict_types=1);
namespace App\Services;

final class CpfCnpjValidator
{
    public function valid(string $document, string $type): bool {
        $n = preg_replace('/\D/', '', $document) ?: '';
        if ($type === 'PF') return $this->cpf($n);
        return $type === 'PJ' && $this->cnpj($n);
    }
    private function cpf(string $v): bool { if (strlen($v)!==11 || preg_match('/^(\d)\1+$/',$v)) return false; for($p=9;$p<11;$p++){ $sum=0; for($i=0;$i<$p;$i++)$sum+=(int)$v[$i]*(($p+1)-$i); $d=(($sum*10)%11)%10; if($d!==(int)$v[$p])return false;} return true; }
    private function cnpj(string $v): bool { if(strlen($v)!==14 || preg_match('/^(\d)\1+$/',$v))return false; foreach([12,13] as $p){$sum=0;$w=$p===12?[5,4,3,2,9,8,7,6,5,4,3,2]:[6,5,4,3,2,9,8,7,6,5,4,3,2];for($i=0;$i<$p;$i++)$sum+=(int)$v[$i]*$w[$i];$d=$sum%11<2?0:11-($sum%11);if($d!==(int)$v[$p])return false;}return true; }
}
