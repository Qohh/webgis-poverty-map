<?php
// auth.php — include di setiap file backend PHP

session_start();

function is_admin(): bool {
    return ($_SESSION['role'] ?? '') === 'admin';
}

function is_logged_in(): bool {
    return isset($_SESSION['user']);
}

// Untuk endpoint READ: semua boleh akses (termasuk guest tanpa session)
// Tidak perlu fungsi require — langsung akses saja

// Untuk API endpoint: tolak jika bukan admin
function require_admin_api(): void {
    if (($_SESSION['role'] ?? '') !== 'admin') {
        header('Content-Type: application/json');
        echo json_encode(["status" => "error", "message" => "Forbidden: admin only"]);
        exit;
    }
}