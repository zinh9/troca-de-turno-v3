<?php

declare(strict_types=1);

namespace TrocaDeTurno\Repositories;

use TrocaDeTurno\Data\Connection;
use TrocaDeTurno\Entities\Apresentacao;

final class ApresentacaoRepository
{
    public function __construct(private readonly Connection $conn) {}

    /** @return array<Apresentacao> */
    public function listarTodos(): array
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT a.*
            FROM apresentacao a
            JOIN empregado e ON a.id_empregado = e.id_empregado
            ORDER BY a.data_hora_apresentacao, e.cargo DESC');
        $stmt->execute();
        return $stmt->fetchAll(); 
    }

    public function listarTodasApresentacoesTurno(): array
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT a.*
            FROM apresentacao a
            JOIN empregado e ON a.id_empregado = e.id_empregado
            WHERE DATEDIFF(HOURS, a.data_hora_apresentacao, SYSDATETIME()) <= 13
            ORDER BY a.data_hora_apresentacao, e.cargo DESC');
        $stmt->execute();
        $result = $stmt->fetchAll();

        if (!$result) {
            return [];
        }

        $apresentacoes = [];

        foreach ($result as $row) {
            $apresentacoes[] = new Apresentacao(
                idApresentacao: (int) $row['id_apresentacao'],
                dataHoraApresentacao: new \DateTimeImmutable($row['data_hora_apresentacao']),
                idEmpregado: (int) $row['id_empregado'],
                idLocal: (int) $row['id_local'],
                status: $row['status'],
                idJustificativa: $row['id_justificativa'] ?? null,
            );
        }

        return $apresentacoes;
    }

    public function buscarPorId(int $idApresentacao): ?Apresentacao
    {
        $stmt = $this->conn->pdo()->prepare('SELECT * FROM apresentacao WHERE id_apresentacao = :idApresentacao');
        $stmt->execute(['idApresentacao' => $idApresentacao]);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        $apresentacao = new Apresentacao(
            idApresentacao: (int) $result['id_apresentacao'],
            dataHoraApresentacao: new \DateTimeImmutable($result['data_hora_apresentacao']),
            idEmpregado: (int) $result['id_empregado'],
            idLocal: (int) $result['id_local'],
            status: $result['status'] ?? '',
            idJustificativa: (int) $result['id_justificativa'] ?? null,
        );

        return $apresentacao;
    }

    public function buscarApresentacoesPorIdEmpregado(int $idEmpregado): ?array
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT * 
            FROM apresentacao 
            WHERE id_empregado = :idEmpregado
            ORDER BY data_hora_apresentacao DESC'
        );
        $stmt->execute(['idEmpregado' => $idEmpregado]);
        $result = $stmt->fetchAll();

        if (!$result) {
            return null;
        }

        $apresentacoes =[];

        foreach ($result as $row) {
            $apresentacoes[] = new Apresentacao(
                idApresentacao: (int) $row['id_apresentacao'],
                dataHoraApresentacao: new \DateTimeImmutable($row['data_hora_apresentacao']),
                idEmpregado: (int) $row['id_empregado'],
                idLocal: (int) $row['id_local'],
                status: $row['status'],
                idJustificativa: $row['id_justificativa'] ?? null,
            );
        }

        return $apresentacoes;
    }

    public function buscarApresentacaoHojePorIdEmpregado(int $idEmpregado): ?Apresentacao
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT TOP 1 id_apresentacao
            FROM apresentacao
            WHERE id_empregado = :idEmpregado
            AND CAST(data_hora_apresentacao AS DATE) = CAST(SYSDATETIME() AS DATE)');

        $stmt->execute([
            'idEmpregado' => $idEmpregado
        ]);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        return new Apresentacao(
            idApresentacao: $result['id_apresentacao'],
            dataHoraApresentacao: new \DateTimeImmutable($result['data_hora_apresentacao']),
            idEmpregado: $result['id_empregado'],
            idLocal: $result['id_local'],
            status: $result['status'] ?? '',
            idJustificativa: (int) $result['id_justificativa'] ?? null,
        );
    }

    public function registrar(int $idEmpregado, int $idLocal): void
    {
        $stmt = $this->conn->pdo()->prepare(
            'INSERT INTO apresentacao (data_hora_apresentacao, id_empregado, id_local) 
            VALUES (SYSDATETIME(), :idEmpregado, :idLocal)'
        );
        $stmt->execute([
            'idEmpregado' => $idEmpregado,
            'idLocal' => $idLocal
        ]);
    }

    public function atualizarStatus(int $idApresentacao, string $novoStatus, ?int $idJustificativa): void
    {
        $stmt = $this->conn->pdo()->prepare(
            'UPDATE apresentacao 
            SET status = :novoStatus,
            id_justificativa = :idJustificativa
            WHERE id_apresentacao = :idApresentacao'
        );
        $stmt->execute([
            'novoStatus' => $novoStatus,
            'idJustificativa' => $idJustificativa,
            'idApresentacao' => $idApresentacao
        ]);
    }
}
