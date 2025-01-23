<?php
session_start();

function checkParticipant() {
    if (!isset($_SESSION['user'])) {
        header('Location:../pages/auth/login.php');
        exit();
    }

    if ($_SESSION['user']['role'] !== 'participant') {
        header('Location:/EventAccess/assets/php/middleware/unauthorized.php');
        exit();
    }
} 