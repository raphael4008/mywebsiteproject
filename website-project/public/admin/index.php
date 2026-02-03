<?php
require_once __DIR__ . '/../../src/Helpers/renders.php';

echo \App\Helpers\render('admin-dashboard', ['title' => 'Admin Dashboard – HouseHunter'], 'dashboard-layout');

