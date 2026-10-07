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

        $linha = $stmt->fetch();

        if (!$linha) {
            return null;
        }

        return $this->mapearEmpregado($linha);
    }

    public function listarTodos(): ?array
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

        $linhas =  $stmt->fetchAll();

        if (!$linhas) {
            return null;
        }

        return $this->mapearTodosEmpregados($linhas);
    }

    public function buscarPorId(int $idEmpregado): ?Empregado
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT e.*, t.turno
            FROM empregado e
            INNER JOIN turno t ON t.id_turno = e.id_turno
            WHERE e.id_empregado = :idEmpregado'
        );
        $stmt->execute(['idEmpregado' => $idEmpregado]);
        $linha = $stmt->fetch();

        return $linha ? $this->mapearEmpregado($linha) : null;
    }

    /** A tabela empregado guarda o turno como id_turno (FK), então atualiza o ID. */
    public function atualizarTurno(int $idEmpregado, int $idTurno): void
    {
        $this->conn->pdo()->prepare(
            'UPDATE empregado
            SET id_turno = :idTurno, data_hora_ultima_atualizacao = SYSDATETIME()
            WHERE id_empregado = :idEmpregado'
        )->execute(['idTurno' => $idTurno, 'idEmpregado' => $idEmpregado]);
    }

    public function listarPorSupervisaoELocal(int $idSupervisao, int $idLocal): ?array
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT 
                e.*,
                t.turno,
                s.supervisao AS supervisao_original
            FROM empregado e
            INNER JOIN supervisao s ON e.id_supervisao = s.id_supervisao
            INNER JOIN turno t ON e.id_turno = t.id_turno
            WHERE e.id_supervisao = :idSupervisao
            '
        );
        $stmt->execute(['idSupervisao' => $idSupervisao]);
        $linhas = $stmt->fetchAll();

        if (!$linhas) {
            return null;
        }

        return $this->mapearTodosEmpregados($linhas);
    }

    private function mapearEmpregado(array $linha): Empregado
    {
        return new Empregado(
            idEmpregado: (int) $linha['id_empregado'],
            nome: $linha['nome'],
            matricula: $linha['matricula'],
            cargo: $linha['cargo'],
            turno: $linha['turno'],
            dataHoraUltimaAtualizacao: ($linha['data_hora_ultima_atualizacao'] ?? null) !== null
                ? new \DateTimeImmutable($linha['data_hora_ultima_atualizacao']) : null,
            idSupervisao: (int) $linha['id_supervisao'],
            idTurno: (int) $linha['id_turno']
        );
    }

    private function mapearTodosEmpregados(array $linhas): array
    {
        $empregados = [];

        foreach($linhas as $linha) {
            $empregados[] = $this->mapearEmpregado(linha: $linha);
        }

        return $empregados;
    }
}