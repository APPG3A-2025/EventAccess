<?php
session_start();

function checkOrganisateur() {
    if (!isset($_SESSION['user'])) {
        header('Location:../pages/auth/login.php');
        exit();
    }

    if ($_SESSION['user']['role'] !== 'organisateur') {
        header('Location:/EventAccess/assets/php/middleware/unauthorized.php');
        exit();
    }
} 