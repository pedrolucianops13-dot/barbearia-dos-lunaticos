<?php

declare(strict_types=1);

require_once __DIR__ . '/resposta.php';
require_once __DIR__ . '/../config/banco.php';
require_once __DIR__ . '/../config/agenda.php';

exigirMetodo('GET');

$datas = datasDaAgenda(agoraNaBarbearia());
if (!isset($_GET['data'])) {
    responderJson(['datas' => $datas]);
}

$data = $_GET['data'];
if (!in_array($data, $datas, true)) {
    responderJson(['erro' => 'Selecione uma data dentro dos dias disponíveis.'], 422);
}

try {
    $consulta = conectarBanco()->prepare('SELECT inicio FROM horarios_ocupados WHERE inicio BETWEEN ? AND ?');
    $consulta->execute([$data . ' 00:00:00', $data . ' 23:59:59']);
    $ocupados = $consulta->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $erro) {
    responderFalhaBanco($erro);
}

$agoraLocal = agoraNaBarbearia()->format('Y-m-d H:i:s');
$horarios = [];
foreach (HORARIOS_AGENDA as $horario) {
    $inicio = "{$data} {$horario}:00";
    if ($inicio > $agoraLocal && !in_array($inicio, $ocupados, true)) {
        $horarios[] = $horario;
    }
}

responderJson(['data' => $data, 'horarios' => $horarios]);
