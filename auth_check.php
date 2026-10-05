<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login
function require_auth() {
    if (empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

// Redirect away from guest pages (login/register)
function require_guest() {
    if (!empty($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }
}