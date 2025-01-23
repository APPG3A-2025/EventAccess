<?php
session_start();

function isAuthenticated() {
    return isset($_SESSION['user']);
}

if (!isAuthenticated()) {
    $response = [
        'authenticated' => false,
        'redirect' => '/pages/auth/login.php'
    ];
} else {
    $response = [
        'authenticated' => true,
        'user' => $_SESSION['user']
    ];
}

echo json_encode($response);
?> 