<?php

namespace App\Database\Seeders;

use PDO;

abstract class BaseSeeder
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    abstract public function run(): void;

    protected function truncate(string $table): void
    {
        $this->pdo->exec("TRUNCATE TABLE `$table`");
    }
}
