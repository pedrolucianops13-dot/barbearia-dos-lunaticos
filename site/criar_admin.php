<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/config/banco.php';

$email = strtolower(trim($argv[1] ?? ''));
if ($argc !== 2 || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, 'Uso: php criar_admin.php email@exemplo.com' . PHP_EOL);
    exit(1);
}

try {
    $banco = conectarBanco();
    $atualizar = $banco->prepare("UPDATE usuarios SET perfil = 'admin' WHERE email = ? AND perfil = 'cliente'");
    $atualizar->execute([$email]);
    if ($atualizar->rowCount() > 0) {
        echo 'Conta promovida a administradora.' . PHP_EOL;
        exit;
    }

    $consulta = $banco->prepare('SELECT perfil FROM usuarios WHERE email = ?');
    $consulta->execute([$email]);
    if ($consulta->fetchColumn() === 'admin') {
        echo 'Esta conta já é administradora.' . PHP_EOL;
        exit;
    }
    fwrite(STDERR, 'Cadastre a conta no site antes de promover.' . PHP_EOL);
} catch (PDOException $erro) {
    fwrite(STDERR, 'Não foi possível atualizar a conta. Verifique o banco e a migração.' . PHP_EOL);
}
exit(1);
