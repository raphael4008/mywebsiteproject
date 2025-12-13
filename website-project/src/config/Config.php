<?php

namespace App\Config;

use Dotenv\Dotenv;

class Config
{
    private static $instance;
    private $data;

    private function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $this->data = $dotenv->load();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }
}
