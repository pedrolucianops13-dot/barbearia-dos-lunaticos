<?php

declare(strict_types=1);

require_once __DIR__ . '/resposta.php';
require_once __DIR__ . '/../config/banco.php';

exigirMetodo('GET');

try {
    $consulta = conectarBanco()->query('SELECT id, codigo, nome, preco FROM servicos WHERE ativo = 1 ORDER BY id');
    responderJson(['servicos' => $consulta->fetchAll()]);
} catch (PDOException $erro) {
    responderFalhaBanco($erro);
}
