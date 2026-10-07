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
        $linhas = $stmt->fetchAll(); 

        if (!$linhas) {
            return [];
        }

        return $this->mapearTodasApresentacoes($linhas);
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
        $linhas = $stmt->fetchAll();

        if (!$linhas) {
            return [];
        }

        return $this->mapearTodasApresentacoes($linhas);
    }

    public function buscarPorId(int $idApresentacao): ?Apresentacao
    {
        $stmt = $this->conn->pdo()->prepare('SELECT * FROM apresentacao WHERE id_apresentacao = :idApresentacao');
        $stmt->execute(['idApresentacao' => $idApresentacao]);
        $linha = $stmt->fetch();

        if (!$linha) {
            return null;
        }

        return $this->mapearApresentacao($linha);
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
        $linhas = $stmt->fetchAll();

        if (!$linhas) {
            return null;
        }

        return $this->mapearTodasApresentacoes($linhas);
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
        $linha = $stmt->fetch();

        if (!$linha) {
            return null;
        }

        return $this->mapearApresentacao($linha);
    }

    public function registrar(int $idEmpregado, int $idLocal, string $status): void
    {
        $stmt = $this->conn->pdo()->prepare(
            'INSERT INTO apresentacao (data_hora_apresentacao, id_empregado, id_local, status) 
            VALUES (SYSDATETIME(), :idEmpregado, :idLocal, :status)'
        );
        $stmt->execute([
            'idEmpregado' => $idEmpregado,
            'idLocal' => $idLocal,
            'status' => $status
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

    private function mapearApresentacao(array $linha): Apresentacao
    {
        return new Apresentacao(
            idApresentacao: (int) $linha['id_apresentacao'],
            dataHoraApresentacao: new \DateTimeImmutable($linha['data_hora_apresentacao']),
            idEmpregado: (int) $linha['id_empregado'],
            idLocal: (int) $linha['id_local'],
            status: $linha['status'] ?? '',
            idJustificativa: (int) $linha['id_justificativa'] ?? null,
        );
    }

    private function mapearTodasApresentacoes(array $linhas): array
    {
        $apresentacoes = [];

        foreach($linhas as $linha) {
            $apresentacoes[] = $this->mapearApresentacao($linha);
        }

        return $apresentacoes;
    }
}
