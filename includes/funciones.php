<?php
require_once __DIR__ . '/../config/db.php';

session_start();

const TIPOS = ['Laptop', 'Proyector', 'Tablet', 'Cámara', 'Laboratorio'];

/** Escapa texto antes de mostrarlo en HTML (evita XSS). */
function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

/** Guarda un mensaje para mostrarlo después de redirigir. */
function flash(string $mensaje): void
{
    $_SESSION['flash'] = $mensaje;
}

function leerFlash(): ?string
{
    $m = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $m;
}

/** Token CSRF para proteger los formularios. */
function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrfValido(?string $token): bool
{
    return is_string($token) && hash_equals($_SESSION['csrf'] ?? '', $token);
}
