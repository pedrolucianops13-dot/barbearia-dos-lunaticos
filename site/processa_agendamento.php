<?php

declare(strict_types=1);

require_once __DIR__ . '/api/resposta.php';
require_once __DIR__ . '/config/agenda.php';
require_once __DIR__ . '/config/banco.php';

exigirMetodo('POST');

$tipo = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
if ($tipo !== 'application/json') {
    responderJson(['erro' => 'Envie o formulário de agendamento pelo site.'], 415);
}

$corpo = file_get_contents('php://input', false, null, 0, 8193);
if ($corpo === false || strlen($corpo) > 8192) {
    responderJson(['erro' => 'Os dados enviados ultrapassam o tamanho permitido.'], 413);
}

try {
    $dados = json_decode($corpo, true, 16, JSON_THROW_ON_ERROR);
} catch (JsonException) {
    responderJson(['erro' => 'Não foi possível ler os dados do formulário.'], 400);
}
if (!is_array($dados)) {
    responderJson(['erro' => 'Não foi possível ler os dados do formulário.'], 400);
}

$servicoId = $dados['servico_id'] ?? null;
$data = $dados['data'] ?? null;
$horario = $dados['horario'] ?? null;
$nome = $dados['nome'] ?? '';
$telefone = $dados['telefone'] ?? '';
$observacoes = $dados['observacoes'] ?? '';

if (!is_int($servicoId) || $servicoId < 1 || $servicoId > 4294967295) {
    responderJson(['erro' => 'Selecione um serviço válido.'], 422);
}

$agora = agoraNaBarbearia();
if (!in_array($data, datasDaAgenda($agora), true) || !in_array($horario, HORARIOS_AGENDA, true)) {
    responderJson(['erro' => 'Selecione uma data e um horário dentro da agenda.'], 422);
}

$inicio = "{$data} {$horario}:00";
if ($inicio <= $agora->format('Y-m-d H:i:s')) {
    responderJson(['erro' => 'Esse horário já passou. Selecione outro horário.'], 409);
}

if (!is_string($nome) || !preg_match('/\A[^\p{Cc}]{2,120}\z/u', trim($nome))) {
    responderJson(['erro' => 'Informe um nome com 2 a 120 caracteres.'], 422);
}
$nome = trim($nome);

if (!is_string($telefone) || !preg_match('/\A(?:\+55[ .-]*)?[0-9() .-]{10,30}\z/', trim($telefone))) {
    responderJson(['erro' => 'Informe um telefone válido com DDD.'], 422);
}
$digitos = preg_replace('/\D/', '', $telefone);
if (preg_match('/\A55[0-9]{10,11}\z/', $digitos)) {
    $digitos = substr($digitos, 2);
} elseif (str_starts_with(trim($telefone), '+')) {
    responderJson(['erro' => 'Informe um telefone válido com DDD.'], 422);
}
if (!preg_match('/\A[0-9]{10,11}\z/', $digitos)) {
    responderJson(['erro' => 'Informe um telefone válido com DDD.'], 422);
}

if (!is_string($observacoes) || !preg_match('/\A.{0,500}\z/us', $observacoes) || str_contains($observacoes, "\0")) {
    responderJson(['erro' => 'Informe observações com até 500 caracteres.'], 422);
}
$observacoes = trim($observacoes);

$banco = null;
try {
    $banco = conectarBanco();
    $consulta = $banco->prepare('SELECT nome, preco FROM servicos WHERE id = ? AND ativo = 1');
    $consulta->execute([$servicoId]);
    $servico = $consulta->fetch();
    if ($servico === false) {
        responderJson(['erro' => 'O serviço selecionado está indisponível.'], 422);
    }

    $banco->beginTransaction();
    $cadastro = $banco->prepare(
        'INSERT INTO agendamentos (servico_id, preco, nome, telefone, observacoes, inicio) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $cadastro->execute([$servicoId, $servico['preco'], $nome, $digitos, $observacoes === '' ? null : $observacoes, $inicio]);
    $agendamentoId = (int) $banco->lastInsertId();

    $ocupar = $banco->prepare('INSERT INTO horarios_ocupados (inicio, agendamento_id) VALUES (?, ?)');
    $ocupar->execute([$inicio, $agendamentoId]);

    if ($inicio <= agoraNaBarbearia()->format('Y-m-d H:i:s')) {
        $banco->rollBack();
        responderJson(['erro' => 'Esse horário já passou. Selecione outro horário.'], 409);
    }

    $banco->commit();
} catch (PDOException $erro) {
    if ($banco?->inTransaction()) {
        $banco->rollBack();
    }
    if ((int) ($erro->errorInfo[1] ?? 0) === 1062) {
        responderJson(['erro' => 'Esse horário acabou de ser ocupado. Selecione outro horário.'], 409);
    }
    responderFalhaBanco($erro);
}

responderJson(['agendamento' => [
    'id' => $agendamentoId,
    'servico' => $servico['nome'],
    'preco' => $servico['preco'],
    'data' => $data,
    'horario' => $horario,
    'nome' => $nome,
    'status' => 'pendente',
]], 201);
