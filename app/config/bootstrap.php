<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// SECURITY: CSRF Protection
require_once __DIR__ . '/csrf.php';
verify_csrf();

// ERROR REPORTING: 0 en producción para evitar romper HTML
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

// DEPENDENCIAS
require_once __DIR__ . '/database.php';

// Cargar functions desde raíz
$funcs = __DIR__ . '/../../functions.php';
if (file_exists($funcs)) { require_once $funcs; }

// SECURITY HEADERS
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");