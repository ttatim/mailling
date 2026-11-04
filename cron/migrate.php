<?php
declare(strict_types=1);
// Run daily at 23:00: 0 23 * * * php /caminho/cron/migrate.php
require dirname(__DIR__).'/app/Core/Autoloader.php';
\App\Core\Autoloader::register(dirname(__DIR__));
$config=require dirname(__DIR__).'/config.php';$db=\App\Core\Database::connect($config);
$db->exec('CREATE TABLE IF NOT EXISTS schema_migrations (filename VARCHAR(255) PRIMARY KEY, applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
foreach(glob(dirname(__DIR__).'/migrations/*.sql') as $file){$name=basename($file);$q=$db->prepare('SELECT 1 FROM schema_migrations WHERE filename=:f');$q->execute(['f'=>$name]);if($q->fetchColumn())continue;try{$db->exec((string)file_get_contents($file));$db->prepare('INSERT INTO schema_migrations(filename) VALUES(:f)')->execute(['f'=>$name]);echo "Applied $name\n";}catch(Throwable $e){fwrite(STDERR,"Migration $name failed: {$e->getMessage()}\n");exit(1);}}
