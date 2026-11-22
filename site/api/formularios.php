<?php

declare(strict_types=1);

require_once __DIR__ . '/resposta.php';

function lerFormularioPublico(): array
{
    exigirMetodo('POST');

    $tipo = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
    if ($tipo !== 'application/json') {
        responderJson(['erro' => 'Envie o formulário pelo site.'], 415);
    }

    $limiteBytes = 32768;
    $corpo = file_get_contents('php://input', false, null, 0, $limiteBytes + 1);
    if ($corpo === false || strlen($corpo) > $limiteBytes) {
        responderJson(['erro' => 'O formulário ultrapassa o tamanho permitido.'], 413);
    }

    try {
        $dados = json_decode($corpo, true, 16, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        responderJson(['erro' => 'Não foi possível ler o formulário.'], 400);
    }
    if (!is_array($dados) || array_is_list($dados)) {
        responderJson(['erro' => 'Não foi possível ler o formulário.'], 400);
    }

    return $dados;
}

function nomeEEmailDoFormulario(array $dados): array
{
    $nome = is_string($dados['nome'] ?? null) ? trim($dados['nome']) : '';
    if (!preg_match('/\A[^\p{Cc}]{2,120}\z/u', $nome) || !preg_match('/\S/u', $nome)) {
        responderJson(['erro' => 'Informe um nome com 2 a 120 caracteres.'], 422);
    }

    $email = is_string($dados['email'] ?? null) ? trim($dados['email']) : '';
    if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        responderJson(['erro' => 'Informe um e-mail válido.'], 422);
    }

    return [$nome, $email];
}
