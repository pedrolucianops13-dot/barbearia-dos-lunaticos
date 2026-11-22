<?php

declare(strict_types=1);

const HORARIOS_AGENDA = [
    '09:00', '10:00', '11:00', '12:00', '13:00', '14:00',
    '15:00', '16:00', '17:00', '18:00', '19:00',
];

function agoraNaBarbearia(): DateTimeImmutable
{
    return new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo'));
}

function datasDaAgenda(DateTimeImmutable $agora): array
{
    $dia = $agora->setTime(0, 0);
    $datas = [];

    while (count($datas) < 7) {
        if ($dia->format('N') !== '7') {
            $datas[] = $dia->format('Y-m-d');
        }
        $dia = $dia->modify('+1 day');
    }

    return $datas;
}
