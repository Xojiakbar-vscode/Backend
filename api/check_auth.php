<?php
session_start();

ini_set('display_errors', 0); // Brauzerga chiqarmaydi
ini_set('log_errors', 1);     // Logga yozadi
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");

// Agar foydalanuvchi sessiyada bo'lsa
if (isset($_SESSION['user'])) {
    echo json_encode([
        "authenticated" => true,
        "user" => $_SESSION['user']
    ]);
} else {
    echo json_encode(["authenticated" => false]);
}
