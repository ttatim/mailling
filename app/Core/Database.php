<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Database
{
    public static function connect(array $config): PDO
    {
        $db = $config['database'];
        $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']};charset={$db['charset']}";
        return new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
