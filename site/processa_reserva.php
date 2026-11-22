<?php

declare(strict_types=1);

require_once __DIR__ . '/api/formularios.php';
require_once __DIR__ . '/config/banco.php';

$dados = lerFormularioPublico();
[$nome, $email] = nomeEEmailDoFormulario($dados);
$produtoId = $dados['produto_id'] ?? null;
if (!is_int($produtoId) || $produtoId < 1 || $produtoId > 4294967295) {
    responderJson(['erro' => 'Selecione um produto válido.'], 422);
}

$banco = null;
try {
    $banco = conectarBanco();
    $banco->beginTransaction();
    $inserir = $banco->prepare(
        'INSERT INTO reservas_produtos (produto_id, nome, email, preco)
         SELECT id, ?, ?, preco FROM produtos WHERE id = ? AND ativo = 1'
    );
    $inserir->execute([$nome, $email, $produtoId]);
    if ($inserir->rowCount() !== 1) {
        $banco->rollBack();
        responderJson(['erro' => 'O produto selecionado está indisponível.'], 404);
    }
    $reservaId = (int) $banco->lastInsertId();

    $consulta = $banco->prepare(
        'SELECT r.id, p.nome AS produto, r.preco
         FROM reservas_produtos r JOIN produtos p ON p.id = r.produto_id WHERE r.id = ?'
    );
    $consulta->execute([$reservaId]);
    $reserva = $consulta->fetch();
    $banco->commit();

    responderJson(['reserva' => $reserva], 201);
} catch (PDOException $erro) {
    if ($banco?->inTransaction()) {
        $banco->rollBack();
    }
    responderFalhaBanco($erro);
}
