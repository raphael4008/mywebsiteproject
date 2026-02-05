<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Helpers/renders.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the base path is set
if (!isset($GLOBALS['basePath'])) {
    $GLOBALS['basePath'] = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
}


\App\Helpers\render('owners', ['title' => 'For Property Owners']);

