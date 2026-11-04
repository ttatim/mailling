<?php
declare(strict_types=1);
namespace App\Services;
final class ContactImporter {
 /** @return array{emails:array<int,string>,found:int,invalid:int,duplicates:int} */
 public function extract(string $content): array { preg_match_all('/(?<![\w.+-])[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,63}(?![\w.-])/i',$content,$m);$valid=[];$bad=0;$seen=[];foreach($m[0] as $email){$email=strtolower(trim($email));if(!filter_var($email,FILTER_VALIDATE_EMAIL)){$bad++;continue;}if(isset($seen[$email]))continue;$seen[$email]=true;$valid[]=$email;}return ['emails'=>$valid,'found'=>count($m[0]),'invalid'=>$bad,'duplicates'=>count($m[0])-count($valid)-$bad]; }
 public function readFile(string $path,string $extension): string { $extension=strtolower($extension);if(in_array($extension,['txt','csv']))return (string)file_get_contents($path);if(in_array($extension,['docx','xlsx'])){$zip=new \ZipArchive();if($zip->open($path)!==true)throw new \RuntimeException('Arquivo ZIP inválido.');$text='';for($i=0;$i<$zip->numFiles;$i++){ $n=$zip->getNameIndex($i);if(str_ends_with($n,'.xml'))$text.=' '.strip_tags((string)$zip->getFromIndex($i));}$zip->close();return $text;}if($extension==='pdf'){return preg_replace('/[^\x20-\x7E@._%+\-]/',' ',(string)file_get_contents($path))??'';}if(in_array($extension,['doc','xls']))return (string)file_get_contents($path);throw new \RuntimeException('Formato não suportado.'); }
}
