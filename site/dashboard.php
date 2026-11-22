<?php

declare(strict_types=1);

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/banco.php';

$administrador = exigirAdministrador();
$token = tokenCsrf();
$mensagem = $_SESSION['mensagem_painel'] ?? null;
unset($_SESSION['mensagem_painel']);
session_write_close();

$rotulosStatus = [
    'pendente' => 'Pendente',
    'confirmado' => 'Confirmado',
    'cancelado' => 'Cancelado',
];
$fuso = new DateTimeZone('America/Sao_Paulo');
$agendamentos = [];
$erroBanco = false;

try {
    $consulta = conectarBanco()->prepare(
        'SELECT a.id, a.nome, a.telefone, a.observacoes, a.inicio, a.status, a.preco,
                s.nome AS servico
         FROM agendamentos a
         INNER JOIN servicos s ON s.id = a.servico_id
         ORDER BY a.inicio DESC, a.id DESC'
    );
    $consulta->execute();
    $agendamentos = $consulta->fetchAll();
} catch (PDOException $erro) {
    error_log('Painel da barbearia: ' . $erro->getMessage());
    $erroBanco = true;
    http_response_code(503);
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Barbearia dos Lunáticos</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<header>
    <nav>
        <div><img src="Imagem/WhatsApp Image 2026-11-15 at 10.41.31.jpeg" alt="Barbearia dos Lunáticos" class="logo-img"></div>
        <ul>
            <li><a href="index.html">Início</a></li>
            <li><a href="servicos.html">Serviços</a></li>
            <li><a href="contato.html">Contato</a></li>
            <li><a href="dashboard.php" aria-current="page">Painel</a></li>
        </ul>
    </nav>
</header>

<main class="container-servicos painel">
    <div class="painel-topo">
        <div>
            <h1>Painel administrativo</h1>
            <p>Bem-vindo, <?= escapar($administrador['nome']) ?>!</p>
        </div>
        <div class="painel-acoes">
            <a class="btn-agendar" href="agendamento.html">Agendar horário</a>
            <form action="logout.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= escapar($token) ?>">
                <button type="submit" class="btn-agendar">Sair</button>
            </form>
        </div>
    </div>
    <section class="box-form painel-lista" aria-labelledby="titulo-agendamentos">
        <h2 id="titulo-agendamentos">Agendamentos</h2>
        <?php if ($mensagem !== null): ?>
            <p class="painel-mensagem" role="status"><?= escapar($mensagem) ?></p>
        <?php endif; ?>

        <?php if ($erroBanco): ?>
            <p class="painel-mensagem" role="alert">Não foi possível carregar os agendamentos. Tente novamente.</p>
        <?php elseif (!$agendamentos): ?>
            <p class="painel-mensagem">Ainda não há agendamentos.</p>
        <?php else: ?>
            <p id="orientacao-tabela" class="painel-orientacao">Em telas estreitas, deslize a tabela para ver as ações.</p>
            <div class="painel-tabela" tabindex="0" role="region" aria-labelledby="titulo-agendamentos" aria-describedby="orientacao-tabela">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Protocolo</th>
                            <th scope="col">Data e hora</th>
                            <th scope="col">Serviço</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Observações</th>
                            <th scope="col">Status</th>
                            <th scope="col">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($agendamentos as $agendamento): ?>
                        <?php
                        $id = (int) $agendamento['id'];
                        $inicio = new DateTimeImmutable($agendamento['inicio'], $fuso);
                        $status = $agendamento['status'];
                        ?>
                        <tr>
                            <th scope="row">#<?= $id ?></th>
                            <td><time datetime="<?= $inicio->format(DATE_ATOM) ?>"><?= $inicio->format('d/m/Y H:i') ?></time></td>
                            <td>
                                <?= escapar($agendamento['servico']) ?>
                                <small>R$ <?= number_format((float) $agendamento['preco'], 2, ',', '.') ?></small>
                            </td>
                            <td>
                                <?= escapar($agendamento['nome']) ?>
                                <small><?= escapar($agendamento['telefone']) ?></small>
                            </td>
                            <td class="painel-observacoes"><?= escapar($agendamento['observacoes'] ?: '—') ?></td>
                            <td><?= escapar($rotulosStatus[$status] ?? $status) ?></td>
                            <td>
                                <?php if ($status !== 'cancelado'): ?>
                                    <form action="gerencia_agendamento.php" method="post" class="painel-acoes">
                                        <input type="hidden" name="agendamento_id" value="<?= $id ?>">
                                        <input type="hidden" name="csrf_token" value="<?= escapar($token) ?>">
                                        <?php if ($status === 'pendente'): ?>
                                            <button class="btn-agendar" type="submit" name="acao" value="confirmar" aria-label="Confirmar agendamento #<?= $id ?>">Confirmar</button>
                                        <?php endif; ?>
                                        <button class="btn-agendar painel-cancelar" type="submit" name="acao" value="cancelar" aria-label="Cancelar agendamento #<?= $id ?>">Cancelar</button>
                                    </form>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<footer>
    <p>&copy; 2026 Barbearia dos Lunáticos - Todos os direitos reservados.</p>
    <p>Seg a Sáb: 09h às 20h | Rua dos Lunáticos, 123</p>
</footer>
<script src="script.js"></script>
</body>
</html>
