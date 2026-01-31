<?php
namespace App\Helpers;

class Cache {
    // Use a portable temp directory inside system temp to avoid hard-coded user paths
    private static $cachePath = null;

    private static function ensurePath()
    {
        if (self::$cachePath === null) {
            self::$cachePath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'househunting_cache';
        }
        if (!is_dir(self::$cachePath)) {
            mkdir(self::$cachePath, 0777, true);
        }
    }

    public static function get($key) {
        self::ensurePath();
        $file = self::$cachePath . DIRECTORY_SEPARATOR . md5($key) . '.cache';
        if (file_exists($file)) {
            $content = @file_get_contents($file); // Use @ to suppress warnings
            if ($content === false) {
                error_log("Failed to read cache file: {$file}");
                return null;
            }
            try {
                $data = unserialize($content);
                if ($data === false && $content !== 'b:0;') { // Check for unserialize failure unless content is boolean false
                    error_log("Failed to unserialize cache data from file: {$file}");
                    return null;
                }
                if (isset($data['expires_at']) && $data['expires_at'] > time()) {
                    return $data['value'];
                }
            } catch (\Exception $e) {
                error_log("Exception during cache unserialization for file {$file}: " . $e->getMessage());
                return null;
            }
            // Cache has expired or was malformed, delete the file
            @unlink($file); // Use @ to suppress warnings if file doesn't exist
        }
        return null;
    }

    public static function set($key, $value, $minutes = 10) {
        self::ensurePath();
        $file = self::$cachePath . DIRECTORY_SEPARATOR . md5($key) . '.cache';
        $data = [
            'expires_at' => time() + ($minutes * 60),
            'value' => $value,
        ];
        $serializedData = serialize($data);
        if (file_put_contents($file, $serializedData) === false) {
            error_log("Failed to write cache file: {$file}");
        }
    }

    public static function forget($key) {
        self::ensurePath();
        $file = self::$cachePath . DIRECTORY_SEPARATOR . md5($key) . '.cache';
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
