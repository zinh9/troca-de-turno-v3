<?php

declare(strict_types=1);

namespace TrocaDeTurno\Repositories;

use PDO;
use TrocaDeTurno\Data\Connection;
use TrocaDeTurno\Entities\Empregado;
use TrocaDeTurno\Entities\Supervisao;
use TrocaDeTurno\Entities\Turno;

final class EmpregadoRepository
{
    public function __construct(private readonly Connection $conn) {}

    public function buscarPorMatricula(string $matricula): ?Empregado
    {
        $sql = "
            SELECT
                e.*,
                s.id_supervisao,
                s.supervisao,
                t.id_turno,
                t.turno
            FROM troca_de_turno.dbo.empregado e
            INNER JOIN dss.dbo.login_dss ld
                ON ld.usuario_dss = e.matricula
            INNER JOIN troca_de_turno.dbo.supervisao s
                ON s.id_supervisao = e.id_supervisao
            INNER JOIN troca_de_turno.dbo.turno t
                ON t.id_turno = e.id_turno
            WHERE e.matricula = ?
        ";

        $stmt = $this->conn->pdo()->prepare($sql);
        $stmt->execute([$matricula]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $supervisao = new Supervisao(
            (int) $row['id_supervisao'],
            $row['supervisao']
        );

        $turno = new Turno(
            (int) $row['id_turno'],
            $row['turno']
        );

        return new Empregado(
            idEmpregado: (int) $row['id_empregado'],
            nome: $row['nome'],
            matricula: $row['matricula'],
            cargo: $row['cargo'],
            turno: $row['turno'],
            dataHoraUltimaAtualizacao: ($row['data_hora_ultima_atualizacao'] !== null
            ? new \DateTimeImmutable($row['data_hora_ultima_atualizacao'])
            : null),
            idSupervisao: $supervisao->idSupervisao,
            idTurno: $turno->idTurno
        );
    }

    public function listarTodos(): array
    {
        $sql = "
            SELECT
                e.*,
                s.id_supervisao,
                s.supervisao,
                t.id_turno,
                t.turno
            FROM troca_de_turno.dbo.empregado e
            INNER JOIN troca_de_turno.dbo.supervisao s
                ON s.id_supervisao = e.id_supervisao
            INNER JOIN troca_de_turno.dbo.turno t
                ON t.id_turno = e.id_turno
        ";

        $stmt = $this->conn->pdo()->prepare($sql);
        $stmt->execute();

        $empregados = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $supervisao = new Supervisao(
                (int) $row['id_supervisao'],
                $row['supervisao']
            );

            $turno = new Turno(
                (int) $row['id_turno'],
                $row['turno']
            );

            $empregado = new Empregado(
                idEmpregado: (int) $row['id_empregado'],
                nome: $row['nome'],
                matricula: $row['matricula'],
                cargo: $row['cargo'],
                turno: $row['turno'],
                dataHoraUltimaAtualizacao: ($row['data_hora_ultima_atualizacao'] !== null
                ? new \DateTimeImmutable($row['data_hora_ultima_atualizacao'])
                : null),
                idSupervisao: $supervisao->idSupervisao,
                idTurno: $turno->idTurno
            );

            $empregados[] = $empregado;
        }

        return $empregados;
    }

    public function atualizarTurno(string $matricula, string $novoTurno): bool
    {
        $sql = "
            UPDATE troca_de_turno.dbo.empregado
            SET turno = ?, data_hora_ultima_atualizacao = GETDATE()
            WHERE matricula = ?
        ";

        $stmt = $this->conn->pdo()->prepare($sql);
        return $stmt->execute([$novoTurno, $matricula]);
    }
}