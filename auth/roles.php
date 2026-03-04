<?php
// auth/roles.php

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // ✅ Evita el doble session_start()
}

function requireRoles(array $rolesPermitidos)
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $role = $_SESSION['role_name'] ?? '';

    if (!in_array($role, $rolesPermitidos, true)) {
        http_response_code(403);
        header('Location: login.php');
        exit;
    }
}

function hasRole(array $rolesPermitidos): bool
{
    $role = $_SESSION['role_name'] ?? '';
    return in_array($role, $rolesPermitidos, true);
}