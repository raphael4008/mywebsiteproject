<?php
namespace App\Config;

use Dotenv\Dotenv;

class Config {
    /**
     * @var bool Flag to ensure Dotenv is only loaded once.
     */
    private static $loaded = false;

    /**
     * Load environment variables from .env file.
     * The file is expected to be in the project root, which is one level up from `src`.
     */
    private static function load() {
        if (self::$loaded) {
            return;
        }

        // The root directory is the parent of the `src` directory's parent.
        // __DIR__ is src/Config, so ../../ is the project root.
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        
        self::$loaded = true;
    }

    /**
     * Get an environment variable or a default value.
     *
     * @param string $key The key of the environment variable.
     * @param mixed|null $default The default value to return if the key is not found.
     * @return mixed The value of the environment variable or the default value.
     */
    public static function get(string $key, $default = null) {
        self::load();
        
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        return $default;
    }
}
