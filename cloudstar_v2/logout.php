<?php
session_start();

// Hancurkan semua session terdaftar
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Tendang pengguna kembali ke halaman utama HTML murni
header("Location: index.html");
exit;
?>