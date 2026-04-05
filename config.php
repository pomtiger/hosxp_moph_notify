<?php
// --- Configuration Setup ---
define('DB_HOST', '10.55.102.102');
define('DB_USER', 'sa');
define('DB_PASS', 'sa10926');
define('DB_NAME', 'hos');

// --- MOPH Notify API Key ---
define('MOPH_URL', 'https://morpromt2f.moph.go.th/api/notify/send');
define('MOPH_CLIENT_KEY', '239fdf62b6f9b795e862a8fca96700ef6cd2675a');
define('MOPH_SECRET_KEY', '2RFPSNYYC2UJWQQ3XDXBY3Z3UITY');

// --- Timezone Setup ---
date_default_timezone_set('Asia/Bangkok');

// --- 1. เชื่อมต่อฐานข้อมูล (ใช้ค่าจาก config.php) ---
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}
?>
