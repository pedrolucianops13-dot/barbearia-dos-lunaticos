<?php

declare(strict_types=1);

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/banco.php';

exigirAdministrador();
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Use o formulário do painel para alterar um agendamento.');
}
if (!csrfValido($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('A confirmação de segurança falhou. Atualize o painel e tente novamente.');
}

function voltarAoPainel(string $mensagem): never
{
    $_SESSION['mensagem_painel'] = $mensagem;
    header('Location: dashboard.php', true, 303);
    exit;
}

$id = filter_var($_POST['agendamento_id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 4294967295],
]);
$acao = $_POST['acao'] ?? null;
if ($id === false || !in_array($acao, ['confirmar', 'cancelar'], true)) {
    voltarAoPainel('Selecione um agendamento e uma ação válidos.');
}

$banco = null;
try {
    $banco = conectarBanco();
    $banco->beginTransaction();

    $consulta = $banco->prepare('SELECT inicio, status FROM agendamentos WHERE id = ? FOR UPDATE');
    $consulta->execute([$id]);
    $agendamento = $consulta->fetch();

    if ($agendamento === false) {
        $banco->rollBack();
        voltarAoPainel('Agendamento não encontrado.');
    }

    $statusAtual = $agendamento['status'];
    $novoStatus = $acao === 'confirmar' ? 'confirmado' : 'cancelado';
    if ($statusAtual === 'cancelado' || $statusAtual === $novoStatus) {
        $banco->rollBack();
        voltarAoPainel('O agendamento já está ' . $statusAtual . '.');
    }

    $atualizar = $banco->prepare('UPDATE agendamentos SET status = ? WHERE id = ?');
    $atualizar->execute([$novoStatus, $id]);
    if ($novoStatus === 'cancelado') {
        $liberar = $banco->prepare('DELETE FROM horarios_ocupados WHERE agendamento_id = ? AND inicio = ?');
        $liberar->execute([$id, $agendamento['inicio']]);
    }

    $banco->commit();
    $mensagem = $novoStatus === 'confirmado'
        ? 'Agendamento confirmado.'
        : 'Agendamento cancelado. O horário foi liberado.';
    voltarAoPainel($mensagem);
} catch (PDOException $erro) {
    if ($banco?->inTransaction()) {
        $banco->rollBack();
    }
    error_log('Alteração de agendamento: ' . $erro->getMessage());
    voltarAoPainel('Não foi possível alterar o agendamento. Tente novamente.');
}
