<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Helpers/renders.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($GLOBALS['basePath'])) {
    $GLOBALS['basePath'] = dirname($_SERVER['SCRIPT_NAME']);
}

\App\Helpers\render('privacy-policy', ['title' => 'Privacy Policy']);