<?php

declare(strict_types=1);

namespace TrocaDeTurno\Repositories;

use TrocaDeTurno\Data\Connection;
use TrocaDeTurno\Entities\HorarioReferencia;

final class HorarioReferenciaRepository
{
    public function __construct(private readonly Connection $conn) {}

    public function buscarPorLocalETurno(int $idLocal, int $idTurno): HorarioReferencia
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT * FROM troca_de_turno.dbo.horario_referecia
            WHERE id_local = :idLocal AND id_turno = :idTurno'
        );
        $stmt->execute(['idLocal' => $idLocal, 'idTurno' => $idTurno]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new \InvalidArgumentException('Horário de referência não encontrado');
        }

        return new HorarioReferencia(
            idHorarioReferencia: (int) $row['id_horario_referencia'],
            idLocal: (int) $row['id_local'],
            idTurno: (int) $row['id_turno'],
            dataHoraReferenciaChegada: new \DateTimeImmutable($row['hora_referencia_chegada']),
            dataHoraReferenciaSaida: new \DateTimeImmutable($row['hora_referencia_saida'])
        );
    }

    public function buscarTodos(): array
    {
        $stmt = $this->conn->pdo()->query(
            'SELECT * FROM troca_de_turno.dbo.horario_referecia'
        );
        $rows = $stmt->fetchAll();

        return array_map(
            fn($row) => new HorarioReferencia(
                idHorarioReferencia: (int) $row['id_horario_referecia'],
                idLocal: (int) $row['id_local'],
                idTurno: (int) $row['id_turno'],
                dataHoraReferenciaChegada: new \DateTimeImmutable($row['data_hora_referencia_chegada']),
                dataHoraReferenciaSaida: new \DateTimeImmutable($row['data_hora_referencia_saida'])
            ),
            $rows
        );
    }
}