<?php

namespace TrocaDeTurno\Repositories;

use TrocaDeTurno\Data\Connection;
use TrocaDeTurno\Entities\Turno;

final class TurnoRepository {
    public function __construct(private readonly Connection $conn) {}

    /** @return array<int, array{id_turno: int, turno: string}> */
    public function listarTodos(): ?array {
        $stmt = $this->conn->pdo()->query("
            SELECT *
            FROM turno
            ORDER BY turno
        ");
        $linhas = $stmt->fetchAll();

        if (!$linhas) {
            return null;
        }

        return $this->mapearTodosTurnos($linhas);
    }

    /** @return array{id_turno: int, turno: string}|null */
    public function buscarPorId(int $idTurno): ?Turno {
        $stmt = $this->conn->pdo()->prepare("
            SELECT *
            FROM turno
            WHERE id_turno = :idTurno
        ");
        $stmt->execute(['idTurno' => $idTurno]);
        $linha = $stmt->fetch();

        if (!$linha) {
            return null;
        }

        return $this->mapearTurno(linha: $linha);
    }

    private function mapearTurno(array $linha): Turno
    {
        return new Turno(
            idTurno: (int) $linha['id_turno'],
            turno: $linha['turno']
        );
    }

    private function mapearTodosTurnos(array $linhas): array
    {   
        $turnos = [];
        foreach($linhas as $linha) {
            $turno = new Turno(
                idTurno: (int) $linha['id_turno'],
                turno: $linha['turno']
            );
        }

        return $turnos;
    }
}

?>