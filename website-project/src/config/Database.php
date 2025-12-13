<?php

namespace App\Config;

use R;

class Database
{
    public static function setup()
    {
        $config = Config::getInstance();
        R::setup(
            'mysql:host=' . $config->get('DB_HOST') . ';dbname=' . $config->get('DB_NAME'),
            $config->get('DB_USER'),
            $config->get('DB_PASS')
        );
    }
}
