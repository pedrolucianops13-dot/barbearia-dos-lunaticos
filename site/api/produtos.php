<?php

declare(strict_types=1);

require_once __DIR__ . '/resposta.php';
require_once __DIR__ . '/../config/banco.php';

exigirMetodo('GET');

$produto = $_GET['produto'] ?? null;
if ($produto !== null && (!is_string($produto) || !preg_match('/\A[^\p{Cc}]{1,120}\z/u', $produto))) {
    responderJson(['erro' => 'Selecione um produto válido.'], 422);
}

try {
    $banco = conectarBanco();
    if ($produto === null) {
        $consulta = $banco->query('SELECT id, codigo, nome, preco FROM produtos WHERE ativo = 1 ORDER BY id');
        responderJson(['produtos' => $consulta->fetchAll()]);
    }

    // Links novos usam o código. O nome ainda funciona nos links antigos.
    $consulta = $banco->prepare(
        'SELECT id, codigo, nome, preco FROM produtos WHERE ativo = 1 AND (codigo = ? OR nome = ?) LIMIT 1'
    );
    $consulta->execute([$produto, $produto]);
    $item = $consulta->fetch();
    if ($item === false) {
        responderJson(['erro' => 'O produto selecionado está indisponível.'], 404);
    }

    responderJson(['produto' => $item]);
} catch (PDOException $erro) {
    responderFalhaBanco($erro);
}
