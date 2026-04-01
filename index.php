<?php
$allowedPages = ['home', 'about', 'services', 'location', 'booking', 'terms'];
$page = $_GET['page'] ?? 'home';

if (!in_array($page, $allowedPages, true)) {
    http_response_code(404);
    $page = 'home';
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/pages/' . $page . '.php';
require __DIR__ . '/includes/footer.php';
