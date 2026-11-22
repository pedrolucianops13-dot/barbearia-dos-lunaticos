<?php

declare(strict_types=1);

require_once __DIR__ . '/config/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Método não permitido.');
}
if (!csrfValido($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('A sessão do formulário expirou. Volte à página e tente novamente.');
}

$_SESSION = [];
$cookie = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires' => time() - 3600,
    'path' => $cookie['path'],
    'domain' => $cookie['domain'],
    'secure' => $cookie['secure'],
    'httponly' => $cookie['httponly'],
    'samesite' => $cookie['samesite'],
]);
session_destroy();

header('Location: login.php', true, 303);
exit;
