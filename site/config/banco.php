<?php

declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');

function conectarBanco(): PDO
{
    $host = getenv('BARBEARIA_DB_HOST') ?: '127.0.0.1';
    $porta = getenv('BARBEARIA_DB_PORT') ?: '3306';
    $banco = getenv('BARBEARIA_DB_NAME') ?: 'barbearia_lunaticos';
    $usuario = getenv('BARBEARIA_DB_USER') ?: 'root';
    $senha = getenv('BARBEARIA_DB_PASSWORD');

    return new PDO(
        "mysql:host={$host};port={$porta};dbname={$banco};charset=utf8mb4",
        $usuario,
        $senha === false ? '' : $senha,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}
