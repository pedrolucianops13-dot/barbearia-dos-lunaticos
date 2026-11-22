<?php

declare(strict_types=1);

require_once __DIR__ . '/config/auth.php';

$usuario = usuarioAtual();
if ($usuario === null) {
    header('Location: login.php', true, 303);
    exit;
}
if ($usuario['perfil'] === 'admin') {
    header('Location: dashboard.php', true, 303);
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha conta - Barbearia dos Lunáticos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <nav>
        <div><img src="Imagem/Logo-Lunaticos-Branca.png" alt="Barbearia dos Lunáticos" class="logo-img"></div>
        <ul>
            <li><a href="index.html">Início</a></li>
            <li><a href="servicos.html">Serviços</a></li>
            <li><a href="contato.html">Contato</a></li>
            <li><a href="conta.php" aria-current="page">Minha conta</a></li>
        </ul>
    </nav>
</header>
<main class="container-formulario">
    <h1>Minha conta</h1>
    <p class="subtitulo">Bem-vindo, <?= escapar($usuario['nome']) ?>!</p>
    <div class="box-form">
        <a href="servicos.html" class="btn-agendar">Ver serviços e agendar</a>
        <form action="logout.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
            <button type="submit" class="btn-enviar">Sair</button>
        </form>
    </div>
</main>
<footer>
    <p>&copy; 2026 Barbearia dos Lunáticos - Todos os direitos reservados.</p>
    <p>Seg a Sáb: 09h às 20h | Rua dos Lunáticos, 123</p>
</footer>
<script src="script.js"></script>
</body>
</html>
