<?php

declare(strict_types=1);

require_once __DIR__ . '/banco.php';

function iniciarSessao(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name('LUNATICOSSESSID');
    $iniciada = @session_start([
        'use_strict_mode' => true,
        'use_only_cookies' => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    header('Cache-Control: no-store');
    if (!$iniciada) {
        error_log('Não foi possível iniciar a sessão da barbearia.');
        http_response_code(503);
        exit('Não foi possível iniciar sua sessão. Tente novamente.');
    }
}

function usuarioAtual(): ?array
{
    iniciarSessao();
    $id = $_SESSION['usuario_id'] ?? null;
    if (!is_int($id)) {
        return null;
    }

    try {
        $consulta = conectarBanco()->prepare('SELECT id, nome, email, perfil FROM usuarios WHERE id = ?');
        $consulta->execute([$id]);
        $usuario = $consulta->fetch();
    } catch (PDOException $erro) {
        error_log('Sessão da barbearia: ' . $erro->getCode());
        http_response_code(503);
        exit('Não foi possível consultar sua conta agora. Tente novamente.');
    }

    if ($usuario === false) {
        unset($_SESSION['usuario_id']);
        return null;
    }
    return $usuario;
}

function iniciarAcesso(int $usuarioId): void
{
    iniciarSessao();
    if (!session_regenerate_id(true)) {
        http_response_code(503);
        exit('Não foi possível iniciar sua sessão. Tente novamente.');
    }
    $_SESSION = ['usuario_id' => $usuarioId];
}

function exigirAdministrador(): array
{
    $usuario = usuarioAtual();
    if ($usuario === null) {
        header('Location: login.php', true, 303);
        exit;
    }
    if ($usuario['perfil'] !== 'admin') {
        http_response_code(403);
        exit('Esta área é exclusiva da administração.');
    }
    return $usuario;
}

function tokenCsrf(): string
{
    iniciarSessao();
    return $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
}

function csrfValido(mixed $token): bool
{
    iniciarSessao();
    $esperado = $_SESSION['csrf_token'] ?? null;
    return is_string($esperado) && is_string($token) && hash_equals($esperado, $token);
}

function escapar(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
