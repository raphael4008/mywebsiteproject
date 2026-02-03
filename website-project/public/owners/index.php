<?php
require_once __DIR__ . '/../../src/Helpers/renders.php';

echo \App\Helpers\render('owner-dashboard', ['title' => 'Owner Dashboard – HouseHunter'], 'dashboard-layout');

