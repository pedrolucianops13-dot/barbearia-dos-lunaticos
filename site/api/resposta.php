<?php

declare(strict_types=1);

function responderJson(array $dados, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

function exigirMetodo(string $metodo): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $metodo) {
        header('Allow: ' . $metodo);
        responderJson(['erro' => 'Método de requisição não permitido.'], 405);
    }
}

function responderFalhaBanco(PDOException $erro): never
{
    error_log('Banco da barbearia: ' . $erro->getMessage());
    responderJson(['erro' => 'O sistema está indisponível no momento. Tente novamente.'], 503);
}
