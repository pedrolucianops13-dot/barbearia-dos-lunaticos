<?php

declare(strict_types=1);

require_once __DIR__ . '/api/formularios.php';
require_once __DIR__ . '/config/banco.php';

$dados = lerFormularioPublico();
[$nome, $email] = nomeEEmailDoFormulario($dados);
$mensagem = is_string($dados['mensagem'] ?? null) ? trim($dados['mensagem']) : '';
if (!preg_match('/\A.{10,3000}\z/us', $mensagem) || str_contains($mensagem, "\0") || !preg_match('/\S/u', $mensagem)) {
    responderJson(['erro' => 'Informe uma mensagem com 10 a 3000 caracteres.'], 422);
}

try {
    $banco = conectarBanco();
    $inserir = $banco->prepare('INSERT INTO mensagens_contato (nome, email, mensagem) VALUES (?, ?, ?)');
    $inserir->execute([$nome, $email, $mensagem]);
    responderJson(['mensagem_id' => (int) $banco->lastInsertId()], 201);
} catch (PDOException $erro) {
    responderFalhaBanco($erro);
}
