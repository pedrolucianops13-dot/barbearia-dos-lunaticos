<?php

declare(strict_types=1);

require_once __DIR__ . '/config/auth.php';

$usuario = usuarioAtual();
if ($usuario !== null) {
    header('Location: ' . ($usuario['perfil'] === 'admin' ? 'dashboard.php' : 'conta.php'), true, 303);
    exit;
}

$cadastro = ($_GET['modo'] ?? '') === 'cadastro';
$acao = $cadastro ? 'login.php?modo=cadastro' : 'login.php';
$titulo = $cadastro ? 'Crie sua conta' : 'Área do Cliente';
$nome = '';
$email = '';
$erro = '';
$metodo = $_SERVER['REQUEST_METHOD'] ?? '';

if ($metodo === 'POST') {
    $nome = is_string($_POST['nome'] ?? null) ? trim($_POST['nome']) : '';
    $email = is_string($_POST['email'] ?? null) ? strtolower(trim($_POST['email'])) : '';
    $senha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';

    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $erro = 'A sessão do formulário expirou. Tente novamente.';
    } elseif ($cadastro && !preg_match('/\A[^\p{Cc}]{2,120}\z/u', $nome)) {
        http_response_code(422);
        $erro = 'Informe um nome com 2 a 120 caracteres.';
    } elseif (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        $erro = 'Informe um e-mail válido.';
    } elseif ($senha === '' || strlen($senha) > 72 || str_contains($senha, "\0") || ($cadastro && !preg_match('/\A.{8,}\z/us', $senha))) {
        http_response_code(422);
        $erro = $cadastro
            ? 'Use uma senha com pelo menos 8 caracteres e no máximo 72 bytes.'
            : 'Informe uma senha válida.';
    } else {
        try {
            $banco = conectarBanco();
            if ($cadastro) {
                $consulta = $banco->prepare('INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)');
                $consulta->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
                iniciarAcesso((int) $banco->lastInsertId());
                header('Location: conta.php', true, 303);
                exit;
            }

            $consulta = $banco->prepare('SELECT id, senha_hash, perfil FROM usuarios WHERE email = ?');
            $consulta->execute([$email]);
            $usuario = $consulta->fetch();
            // Verifica um hash também quando o e-mail não existe.
            $hash = $usuario['senha_hash'] ?? '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
            if (password_verify($senha, $hash) && $usuario !== false) {
                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    $atualizar = $banco->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ? AND senha_hash = ?');
                    $atualizar->execute([password_hash($senha, PASSWORD_DEFAULT), $usuario['id'], $hash]);
                }
                iniciarAcesso((int) $usuario['id']);
                header('Location: ' . ($usuario['perfil'] === 'admin' ? 'dashboard.php' : 'conta.php'), true, 303);
                exit;
            }
            http_response_code(401);
            $erro = 'E-mail ou senha incorretos.';
        } catch (PDOException $falha) {
            if ($cadastro && (int) ($falha->errorInfo[1] ?? 0) === 1062) {
                http_response_code(409);
                $erro = 'Já existe uma conta com este e-mail. Use a opção de entrar.';
            } else {
                error_log('Acesso da barbearia: ' . $falha->getCode());
                http_response_code(503);
                $erro = 'O acesso está indisponível no momento. Tente novamente.';
            }
        }
    }
} elseif ($metodo !== 'GET' && $metodo !== 'HEAD') {
    header('Allow: GET, HEAD, POST');
    http_response_code(405);
    exit('Método não permitido.');
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escapar($titulo) ?> - Barbearia dos Lunáticos</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .aviso-acesso { background: #fff; border: 1px solid #d4af37; border-radius: 5px; color: #353023; margin: 0 0 16px; padding: 12px; }
        .links-acesso { margin-top: 16px; text-align: center; }
        .links-acesso a { color: #1a1a1a; font-weight: bold; }
        .ajuda-senha { display: block; color: #555; margin-top: 5px; }
    </style>
</head>
<body>
<header>
    <nav>
        <div><img src="Imagem/Logo-Lunaticos-Branca.png" alt="Barbearia dos Lunáticos" class="logo-img"></div>
        <ul>
            <li><a href="index.html">Início</a></li>
            <li><a href="servicos.html">Serviços</a></li>
            <li><a href="contato.html">Contato</a></li>
            <li><a href="login.php"<?= $cadastro ? '' : ' aria-current="page"' ?>>Login</a></li>
        </ul>
    </nav>
</header>
<main class="container-formulario">
    <h1><?= escapar($titulo) ?></h1>
    <p class="subtitulo"><?= $cadastro ? 'Preencha seus dados para criar a conta.' : 'Faça login para acessar sua conta.' ?></p>
    <form id="form-login" action="<?= escapar($acao) ?>" method="post" class="box-form">
        <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
        <?php if ($erro !== ''): ?>
            <p class="aviso-acesso" role="alert"><?= escapar($erro) ?></p>
        <?php endif; ?>
        <?php if ($cadastro): ?>
            <div class="campo">
                <label for="nome-cadastro">Nome completo</label>
                <input type="text" id="nome-cadastro" name="nome" placeholder="Digite seu nome" value="<?= escapar($nome) ?>" autocomplete="name" minlength="2" maxlength="120" required>
            </div>
        <?php endif; ?>
        <div class="campo">
            <label for="email-login">E-mail</label>
            <input type="email" id="email-login" name="email" placeholder="cliente@lunaticos.com" value="<?= escapar($email) ?>" autocomplete="username" maxlength="254" required>
        </div>
        <div class="campo">
            <label for="senha-login">Senha</label>
            <input type="password" id="senha-login" name="senha" placeholder="Digite sua senha" autocomplete="<?= $cadastro ? 'new-password' : 'current-password' ?>" <?= $cadastro ? 'minlength="8" aria-describedby="ajuda-senha"' : '' ?> required>
            <?php if ($cadastro): ?>
                <small id="ajuda-senha" class="ajuda-senha">Use pelo menos 8 caracteres.</small>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn-enviar" id="btn-entrar"><?= $cadastro ? 'Criar conta' : 'Entrar' ?></button>
        <?php if ($cadastro): ?>
            <p class="links-acesso">Já tem uma conta? <a href="login.php">Entrar</a></p>
        <?php else: ?>
            <p class="links-acesso">Ainda não tem conta? <a href="login.php?modo=cadastro">Cadastre-se</a></p>
        <?php endif; ?>
    </form>
</main>
<footer>
    <p>&copy; 2026 Barbearia dos Lunáticos - Todos os direitos reservados.</p>
    <p>Seg a Sáb: 09h às 20h | Rua dos Lunáticos, 123</p>
</footer>
<script src="script.js"></script>
</body>
</html>
