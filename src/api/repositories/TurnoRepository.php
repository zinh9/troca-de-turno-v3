<?php

use TrocaDeTurno\Data\Connection;

final class TurnoRepository {
    public function __construct(private readonly Connection $conn) {}

    /** @return array<int, array{id_turno: int, turno: string}> */
    public function listarTodos() {
        $stmt = $this->conn->pdo()->query("
            SELECT *
            FROM turno
            ORDER BY turno
        ");

        return $stmt->fetchAll();
    }

    /** @return array{id_turno: int, turno: string}|null */
    public function buscarPorId(int $idTurno) {
        $stmt = $this->conn->pdo()->prepare("
            SELECT *
            FROM turno
            WHERE id_turno = :idTurno
        ");
        $stmt->execute(['idTurno' => $idTurno]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return [
            'id_turno' => $row['id_turno'],
            'turno' => $row['turno']
        ];
    }
}

?>